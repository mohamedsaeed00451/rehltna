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
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
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
                    if ($value !== 'cash_on_delivery') {
                        if (!PaymentMethod::where('code', $value)->exists()) {
                            $fail('The selected payment method is invalid.');
                        }
                    }
                },
            ],
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

        if ($paymentCode !== 'cash_on_delivery') {
            $paymentMethodModel = PaymentMethod::query()
                ->where('code', $paymentCode)
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

            foreach ($calculation['order_items_data'] as $item) {
                $order->items()->create($item);
            }

            DB::commit();

            $paymentBaseUrl = null;

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

            if ($paymentCode === 'tamara') {

                if (!$paymentMethodModel) throw new \Exception('Payment configuration missing');

                $responsePayload['message'] = 'Redirect to payment gateway';
                $responsePayload['action'] = 'redirect';
                $responsePayload['payment_method'] = 'tamara';

                $amount = $calculation['final_total'];

                $callbackParams = [
                    'source' => $source,
                    'order_id' => $order->id,
                    'transaction_token' => $securityToken,
                    'tenant_id' => $request->header('X-Tenant-ID') ?? $request->get('tenant_id')
                ];

                try {

                    $data = [
                        'total_amount' =>
                            [
                                'amount' => (string)number_format($amount, 2, '.', ''),
                                'currency' => "SAR",
                            ],
                        'shipping_amount' =>
                            [
                                'amount' => (string)number_format(0, 2, '.', ''),
                                'currency' => "SAR",
                            ],
                        'tax_amount' =>
                            [
                                'amount' => (string)number_format(0, 2, '.', ''),
                                'currency' => "SAR",
                            ],
                        'order_reference_id' => $order->transaction_token,
                        'order_number' => $order->id,
                        'discount' =>
                            [
                                'name' => "Discount Name",
                                'amount' =>
                                    [
                                        'amount' => $order->discount_amount,
                                        'currency' => "SAR",
                                    ],
                            ],
                        'consumer' =>
                            [
                                'first_name' => $order->name,
                                'last_name' => $order->name,
                                'phone_number' => $order->phone,
                                'email' => $order->email,
                            ],
                        'country_code' => "SA",
                        'description' => 'Order #' . $order->transaction_token . ' from ' . env('APP_NAME', 'Rehltna'),
                        'merchant_url' =>
                            [
                                'success' => route('payment.success', $callbackParams),
                                'failure' => route('payment.cancel', $callbackParams),
                                'cancel' => route('payment.cancel', $callbackParams),
                                'notification' => null,
                            ],
                        'payment_type' => 'PAY_BY_INSTALMENTS',
                        'instalments' => 3,
                        'billing_address' =>
                            [
                                'first_name' => $order->name,
                                'last_name' => $order->name,
                                'line1' => 'Address line 1',
                                'city' => 'city',
                                'country_code' => 'SA',
                                'phone_number' => $order->phone,
                            ],
                        'shipping_address' =>
                            [
                                'first_name' => $order->name,
                                'last_name' => $order->name,
                                'line1' => 'Address line 1',
                                'city' => 'city',
                                'country_code' => 'SA',
                                'phone_number' => $order->phone,
                            ],
                        'locale' => 'en_US',
                        'items' => $order->items,
                    ];

                    $client = new Client([
                        'base_uri' => $paymentBaseUrl,
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

            } elseif (str_contains($paymentCode, 'bank_transfer')) {

                if (!$paymentMethodModel) throw new \Exception('Payment configuration missing');

                $responsePayload['message'] = 'Please transfer amount and upload receipt';
                $responsePayload['action'] = 'upload_receipt';
                $responsePayload['payment_method'] = $paymentCode;
                $responsePayload['transfer_details'] = $paymentBaseUrl;

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
            'receipt' => 'required|image|max:4096', // 4MB Max
        ]);

        $order = Order::query()->find($request->get('order_id'));

        if ($request->get('transaction_token') !== $order->transaction_token)
            return $this->responseMessage(400, 'Not Authorized to Upload Receipt for this order');

        if ($order->payment_status === 'paid')
            return $this->responseMessage(400, 'Order is already paid');

        if ($order->payment_status === 'reviewing')
            return $this->responseMessage(400, 'This order is already under reviewed. please wait for review');

        if (!str_contains($order->payment_method, 'bank_transfer'))
            return $this->responseMessage(400, 'This payment method is not bank transfer');

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
                return redirect()->away(
                    $payment_failed_url . '?' . http_build_query([
                        'order_id' => $order->id
                    ])
                );
            }

            $order->update([
                'payment_status' => 'paid'
            ]);

            try {
                $order->load('items.item');
                Mail::to($order->email)->send(new OrderInvoiceMail($order));
            } catch (\Exception $e) {
                Log::error('Payment Success Mail Error: ' . $e->getMessage());
            }

            return redirect()->away(
                $payment_success_url . '?' . http_build_query([
                    'order_id' => $order->id
                ])
            );

        } catch (\Exception $e) {
            return redirect()->away(
                $payment_failed_url . '?' . http_build_query([
                    'order_id' => $request->get('order_id')
                ])
            );
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

}
