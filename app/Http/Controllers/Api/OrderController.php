<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Mail\OrderInvoiceMail;
use App\Mail\OrderUnderReviewMail;
use App\Models\Coupon;
use App\Models\Item;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\ResidencyUser;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    use ResponseTrait;

    private function calculateOrderDetails($itemsRequest, $couponCode = null): array
    {
        $itemIds = collect($itemsRequest)->pluck('item_id')->toArray();
        $dbItems = Item::query()->whereIn('id', $itemIds)->get()->keyBy('id');

        $subTotal = 0;
        $orderItemsData = [];

        foreach ($itemsRequest as $reqItem) {
            $dbItem = $dbItems->get($reqItem['item_id']);
            if (!$dbItem) continue;
            $itemTotal = $dbItem->price * $reqItem['attendees'];
            $subTotal += $itemTotal;
            $orderItemsData[] = [
                'item_id' => $dbItem->id,
                'attendees_count' => $reqItem['attendees'],
                'price_per_unit' => $dbItem->price,
                'total' => $itemTotal,
            ];
        }

        $discountAmount = 0;
        $couponId = null;

        if ($couponCode) {
            $coupon = Coupon::with('items')->where('code', $couponCode)->first();
            if ($coupon && $coupon->isValid()) {
                $couponId = $coupon->id;
                $allowedItemIds = $coupon->items->pluck('id')->toArray();
                if (count($allowedItemIds) > 0) {
                    foreach ($orderItemsData as $data) {
                        if (in_array($data['item_id'], $allowedItemIds)) {
                            if ($coupon->type == 'percent') {
                                $discountAmount += ($data['total'] * $coupon->value) / 100;
                            } else {
                                $discountAmount += min($coupon->value, $data['total']);
                            }
                        }
                    }
                } else {
                    if ($coupon->type == 'percent') {
                        $discountAmount = ($subTotal * $coupon->value) / 100;
                    } else {
                        $discountAmount = min($coupon->value, $subTotal);
                    }
                }
            }
        }

        $finalTotal = max(0, $subTotal - $discountAmount);

        return [
            'sub_total' => $subTotal,
            'discount_amount' => $discountAmount,
            'coupon_id' => $couponId,
            'final_total' => $finalTotal,
            'order_items_data' => $orderItemsData
        ];
    }

    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'source' => 'required|in:web,app',
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'payment_method_code' => [
                'required',
                function ($attribute, $value, $fail) {
                    if ($value !== 'cash_on_delivery' && $value !== 'apple_pay') {
                        if (!PaymentMethod::where('code', $value)->exists()) {
                            $fail('The selected payment method is invalid.');
                        }
                    }
                },
            ],
            'apple_pay_token' => 'required_if:payment_method_code,apple_pay',
            'coupon_code' => [
                'nullable',
                Rule::exists(Coupon::class, 'code'),
            ],
            'items' => 'required|array',
            'items.*.item_id' => [
                'required',
                Rule::exists(Item::class, 'id'),
            ],
            'items.*.attendees' => 'required|integer|min:1',
        ]);

        $source = $request->get('source');
        $paymentCode = $request->get('payment_method_code');
        $paymentMethodModel = null;

        $searchCode = ($paymentCode === 'apple_pay') ? 'moyasar' : $paymentCode;

        if ($paymentCode !== 'cash_on_delivery') {
            $paymentMethodModel = PaymentMethod::query()
                ->where('code', $searchCode)
                ->where('status', 1)
                ->firstOrFail();
        }

        $calculation = $this->calculateOrderDetails($request->get('items'), $request->get('coupon_code'));

        DB::beginTransaction();
        try {

            $securityToken = Str::random(40);

            $order = Order::query()->create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'payment_method' => $paymentCode,
                'payment_status' => 'pending',
                'sub_total' => $calculation['sub_total'],
                'discount_amount' => $calculation['discount_amount'],
                'coupon_id' => $calculation['coupon_id'],
                'total_amount' => $calculation['final_total'],
                'transaction_token' => $securityToken,
            ]);

            $user = ResidencyUser::withTrashed()->where('email', $request->get('email'))->first();

            if ($user) {
                if ($user->trashed()) {
                    $user->restore();
                }
            } else {
                $user = ResidencyUser::query()->create([
                    'email' => $request->get('email'),
                    'name' => $request->get('name'),
                    'phone' => $request->get('phone'),
                    'password' => Hash::make(12345678),
                ]);
            }

            $user->orders()->save($order);

            foreach ($calculation['order_items_data'] as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            $paymentBaseUrl = null;
            $secretKey = null;

            if ($paymentMethodModel) {
                $config = $paymentMethodModel->config;
                $mode = $config['mode'] ?? 'test';
                $paymentBaseUrl = $config[$mode]['base_url'] ?? null;
                $secretKey = $config[$mode]['secret_key'] ?? null;
            }

            $responsePayload = [
                'order_id' => $order->id,
                'transaction_token' => $securityToken,
                'sub_total' => $calculation['sub_total'],
                'discount' => $calculation['discount_amount'],
                'total_amount' => $calculation['final_total'],
                'status' => 'pending',
            ];

            if ($paymentCode === 'moyasar' || $paymentCode === 'apple_pay') {

                if (!$secretKey) throw new \Exception('Moyasar secret key missing');

                $amountInHalalas = (int)round($calculation['final_total'] * 100);

                $client = new Client([
                    'base_uri' => 'https://api.moyasar.com/v1/',
                    'auth' => [$secretKey, ''],
                    'headers' => ['Content-Type' => 'application/json'],
                ]);

                if ($paymentCode === 'apple_pay' && $request->has('apple_pay_token')) {
                    $response = $client->post('payments', [
                        'json' => [
                            'amount' => $amountInHalalas,
                            'currency' => 'SAR',
                            'description' => 'Order #' . $order->id,
                            'source' => [
                                'type' => 'applepay',
                                'token' => $request->get('apple_pay_token')
                            ]
                        ],
                    ]);

                    $response_decode = json_decode($response->getBody()->getContents(), true);

                    if (isset($response_decode['status']) && $response_decode['status'] === 'paid') {
                        $this->handleOrderSuccess($order);

                        $responsePayload['message'] = 'Payment successful via Apple Pay';
                        $responsePayload['action'] = 'success';
                        $responsePayload['status'] = 'paid';
                    } else {
                        throw new \Exception($response_decode['message'] ?? 'Payment declined.');
                    }

                } else {
                    $callbackParams = [
                        'source' => $source,
                        'order_id' => $order->id,
                        'transaction_token' => $securityToken,
                        'tenant_id' => $request->header('X-Tenant-ID') ?? $request->get('tenant_id')
                    ];

                    $response = $client->post('invoices', [
                        'json' => [
                            'amount' => $amountInHalalas,
                            'currency' => 'SAR',
                            'description' => 'Order #' . $order->id,
                            'success_url' => route('payment.success', $callbackParams),
                            'back_url' => route('payment.cancel', $callbackParams),
                        ],
                    ]);

                    $response_decode = json_decode($response->getBody()->getContents(), true);

                    if (isset($response_decode['url'])) {
                        $responsePayload['message'] = 'Redirect to Moyasar payment gateway';
                        $responsePayload['action'] = 'redirect';
                        $responsePayload['transfer_details'] = $response_decode['url'];
                    } else {
                        throw new \Exception('Could not generate Moyasar payment link');
                    }
                }

            } elseif ($paymentCode === 'tamara') {

                if (!$paymentMethodModel) throw new \Exception('Payment configuration missing');

                $responsePayload['message'] = 'Redirect to payment gateway';
                $responsePayload['action'] = 'redirect';
                $responsePayload['payment_method'] = 'tamara';

                $amount = (float)$calculation['final_total'];

                $callbackParams = [
                    'source' => $source,
                    'order_id' => $order->id,
                    'transaction_token' => $securityToken,
                    'tenant_id' => $request->header('X-Tenant-ID') ?? $request->get('tenant_id')
                ];

                $tamaraItems = [];
                foreach ($order->items as $item) {
                    $tamaraItems[] = [
                        'reference_id' => (string)$item->id,
                        'type' => 'Digital',
                        'name' => 'Item #' . $item->item_id,
                        'sku' => 'SKU-' . $item->item_id,
                        'quantity' => (int)$item->attendees_count,
                        'unit_price' => [
                            'amount' => (float)$item->price_per_unit,
                            'currency' => "SAR"
                        ],
                        'discount_amount' => [
                            'amount' => 0.00,
                            'currency' => "SAR"
                        ],
                        'tax_amount' => [
                            'amount' => 0.00,
                            'currency' => "SAR"
                        ],
                        'total_amount' => [
                            'amount' => (float)$item->total,
                            'currency' => "SAR"
                        ]
                    ];
                }

                try {
                    $data = [
                        'total_amount' => [
                            'amount' => $amount,
                            'currency' => "SAR",
                        ],
                        'shipping_amount' => [
                            'amount' => 0.00,
                            'currency' => "SAR",
                        ],
                        'tax_amount' => [
                            'amount' => 0.00,
                            'currency' => "SAR",
                        ],
                        'order_reference_id' => (string)$order->transaction_token,
                        'order_number' => (string)$order->id,
                        'consumer' => [
                            'first_name' => (string)$order->name,
                            'last_name' => (string)$order->name,
                            'phone_number' => (string)$order->phone,
                            'email' => (string)$order->email,
                        ],
                        'country_code' => "SA",
                        'description' => 'Order #' . $order->id . ' from ' . env('APP_NAME', 'Rehltna'),
                        'merchant_url' => [
                            'success' => route('payment.success', $callbackParams),
                            'failure' => route('payment.cancel', $callbackParams),
                            'cancel' => route('payment.cancel', $callbackParams),
                            'notification' => route('payment.success', $callbackParams),
                        ],
                        'payment_type' => 'PAY_BY_INSTALMENTS',
                        'instalments' => 3,
                        'billing_address' => [
                            'first_name' => (string)$order->name,
                            'last_name' => (string)$order->name,
                            'line1' => 'Address line 1',
                            'city' => 'Riyadh',
                            'country_code' => 'SA',
                            'phone_number' => (string)$order->phone,
                        ],
                        'shipping_address' => [
                            'first_name' => (string)$order->name,
                            'last_name' => (string)$order->name,
                            'line1' => 'Address line 1',
                            'city' => 'Riyadh',
                            'country_code' => 'SA',
                            'phone_number' => (string)$order->phone,
                        ],
                        'locale' => 'ar_SA',
                        'items' => $tamaraItems,
                    ];

                    if ((float)$order->discount_amount > 0) {
                        $data['discount'] = [
                            'name' => "Order Discount",
                            'amount' => [
                                'amount' => (float)$order->discount_amount,
                                'currency' => "SAR",
                            ],
                        ];
                    }

                    $client = new Client([
                        'base_uri' => rtrim($paymentBaseUrl, '/') . '/',
                        'headers' => [
                            'Authorization' => 'Bearer ' . $secretKey,
                            'Content-Type' => 'application/json',
                        ],
                    ]);

                    $response = $client->post('checkout', [
                        'json' => $data,
                    ]);

                    $response_decode = json_decode($response->getBody()->getContents(), true);

                    if (isset($response_decode['checkout_id']) && $response_decode['checkout_id'] != '') {
                        $responsePayload['transfer_details'] = $response_decode['checkout_url'];
                    } else {
                        $responsePayload['transfer_details'] = "Payment Gateway Error";
                    }

                } catch (\Exception $e) {
                    $responsePayload['transfer_details'] = $e->getMessage();
                }

            } elseif (str_contains($paymentCode, 'bank_transfer') || str_contains($paymentCode, 'wallet') || $paymentCode === 'instapay') {

                if (!$paymentMethodModel) throw new \Exception('Payment configuration missing');

                $responsePayload['message'] = 'Please transfer amount and upload receipt';
                $responsePayload['action'] = 'upload_receipt';
                $responsePayload['payment_method'] = $paymentCode;
                $responsePayload['transfer_details'] = $paymentMethodModel->config;

            } elseif ($paymentCode === 'cash_on_delivery') {

                $responsePayload['message'] = 'Order placed successfully. Please wait for confirmation.';
                $responsePayload['action'] = 'none';
                $responsePayload['payment_method'] = 'cash_on_delivery';
                $responsePayload['transfer_details'] = null;
            }

            return $this->responseMessage(200, 'Order Created Successfully', $responsePayload);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->responseMessage(500, 'Error: ' . $e->getMessage());
        }
    }

    public function uploadReceipt(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => [
                'required',
                Rule::exists(Order::class, 'id'),
            ],
            'receipt' => 'required|image|max:4096',
        ]);

        $order = Order::query()->find($request->get('order_id'));

        if ($request->get('transaction_token') !== $order->transaction_token)
            return $this->responseMessage(400, 'Not Authorized to Upload Receipt for this order');

        if ($order->payment_status === 'paid')
            return $this->responseMessage(400, 'Order is already paid');

        if ($order->payment_status === 'reviewing')
            return $this->responseMessage(400, 'This order is already under reviewed. please wait for review');

        if (!str_contains($order->payment_method, 'bank_transfer') && !str_contains($order->payment_method, 'wallet') && $order->payment_method !== 'instapay') {
            return $this->responseMessage(400, 'This payment method does not require a receipt');
        }

        if ($request->hasFile('receipt')) {
            deleteFiles([$order->payment_proof]);
            $path = uploadFile($request->file('receipt'), 'payment-proofs', 'receipt');
            $order->update([
                'payment_proof' => $path,
                'payment_status' => 'reviewing'
            ]);

            try {
                Mail::to($order->email)->send(new OrderUnderReviewMail($order));
            } catch (\Exception $e) {
                Log::error('Review Mail Error: ' . $e->getMessage());
            }

            return $this->responseMessage(200, 'Receipt uploaded successfully. Waiting for admin approval.', $order->load('items.item.itemType'));
        }

        return $this->responseMessage(400, 'No file uploaded');
    }

    public function checkCoupon(Request $request): JsonResponse
    {
        $request->validate([
            'coupon_code' => [
                'nullable',
                Rule::exists(Coupon::class, 'code'),
            ],
            'items' => 'required|array',
            'items.*.item_id' => [
                'required',
                Rule::exists(Item::class, 'id'),
            ],
            'items.*.attendees' => 'required|integer|min:1',
        ]);

        $calculation = $this->calculateOrderDetails($request->get('items'), $request->get('coupon_code'));

        $responsePayload = [
            'sub_total' => $calculation['sub_total'],
            'discount' => $calculation['discount_amount'],
            'total_amount' => $calculation['final_total'],
        ];

        return $this->responseMessage(200, 'Coupon Code Discount', $responsePayload);
    }

    public function success(Request $request): RedirectResponse
    {
        $payment_success_url = get_setting('payment_success_url');
        $payment_failed_url = get_setting('payment_failed_url');

        if ($request->get('source') == 'app') {
            $payment_success_url = get_setting('payment_success_url_app');
            $payment_failed_url = get_setting('payment_failed_url_app');
        }

        try {
            $order = Order::query()->findOrFail($request->get('order_id'));

            if ($request->get('transaction_token') !== $order->transaction_token) {
                return redirect()->away($payment_failed_url . '?' . http_build_query(['order_id' => $order->id]));
            }

            if ($request->has('status') && strtolower($request->get('status')) !== 'paid') {
                $order->update(['payment_status' => 'failed']);
                return redirect()->away($payment_failed_url . '?' . http_build_query(['order_id' => $order->id]));
            }

            if ($order->payment_status !== 'paid') {
                $this->handleOrderSuccess($order);
            }

            return redirect()->away($payment_success_url . '?' . http_build_query(['order_id' => $order->id]));

        } catch (\Exception $e) {
            return redirect()->away($payment_failed_url . '?' . http_build_query(['order_id' => $request->get('order_id')]));
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        $payment_cancel_url = get_setting('payment_cancel_url');
        $payment_failed_url = get_setting('payment_failed_url');
        if ($request->get('source') == 'app') {
            $payment_cancel_url = get_setting('payment_cancel_url_app');
            $payment_failed_url = get_setting('payment_failed_url_app');
        }
        $order = Order::query()->find($request->get('order_id'));
        if ($request->get('transaction_token') !== $order->transaction_token) {
            return redirect()->away(
                $payment_failed_url . '?' . http_build_query([
                    'order_id' => $order->id
                ])
            );
        }
        $order->update([
            'payment_status' => 'canceled'
        ]);
        return redirect()->away(
            $payment_cancel_url . '?' . http_build_query([
                'order_id' => $request->get('order_id')
            ])
        );
    }

    public function getOrderById($id): JsonResponse
    {
        $order = Order::query()->with('items.item.itemType', 'items.item.itineraries.city')->findOrFail($id);
        return $this->responseMessage(200, 'Order Found', $order);
    }

    private function handleOrderSuccess(Order $order): void
    {
        $order->update([
            'payment_status' => 'paid'
        ]);

        try {
            $order->load(['items.item', 'user.package']);
            $user = $order->user;

            if ($user) {
                $basePoints = 0;

                foreach ($order->items as $orderItem) {
                    if ($orderItem->item && $orderItem->item->earned_points) {
                        $basePoints += $orderItem->item->earned_points;
                    }

                    $existing = DB::table('item_residency_users')
                        ->where('residency_user_id', $user->id)
                        ->where('item_id', $orderItem->item_id)
                        ->first();

                    if ($existing) {
                        DB::table('item_residency_users')
                            ->where('id', $existing->id)
                            ->update([
                                'attendees' => (int)$existing->attendees + (int)$orderItem->attendees_count,
                                'updated_at' => now()
                            ]);
                    } else {
                        DB::table('item_residency_users')->insert([
                            'residency_user_id' => $user->id,
                            'item_id' => $orderItem->item_id,
                            'attendees' => $orderItem->attendees_count,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                if ($basePoints > 0) {
                    $multiplier = $user->package ? $user->package->points_multiplier : 1.00;
                    $finalPoints = $basePoints * $multiplier;
                    $user->increment('earned_points', $finalPoints);
                    $user->increment('available_points', $finalPoints);
                }
            }
        } catch (\Exception $e) {
            Log::error('Points Calculation Error: ' . $e->getMessage());
        }

        try {
            Mail::to($order->email)->send(new OrderInvoiceMail($order));
        } catch (\Exception $e) {
            Log::error('Payment Success Mail Error: ' . $e->getMessage());
        }
    }
}
