<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Mail\OrderInvoiceMail;
use App\Mail\OrderUnderReviewMail;
use App\Models\Coupon;
use App\Models\Item;
use App\Models\ItemPackage;
use App\Models\Order;
use App\Models\PaymentMethod;
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
        $subTotal = 0;
        $orderItemsData = [];

        foreach ($itemsRequest as $reqItem) {
            $dbItem = Item::query()->find($reqItem['item_id']);
            if (!$dbItem) continue;

            $price = $dbItem->price;
            $pkgId = $reqItem['item_package_id'] ?? null;

            if ($pkgId) {
                $package = ItemPackage::query()
                    ->where('id', $pkgId)
                    ->where('item_id', $dbItem->id)
                    ->first();
                if ($package) {
                    $price = $package->price;
                }
            }

            $itemTotal = $price * $reqItem['attendees'];
            $subTotal += $itemTotal;

            $orderItemsData[] = [
                'item_id' => $dbItem->id,
                'item_package_id' => $pkgId,
                'attendees_count' => $reqItem['attendees'],
                'price_per_unit' => $price,
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
            'name' => 'required|string',
            'email' => 'required|email',
            'phone' => 'required',
            'payment_method_code' => [
                'required',
                Rule::exists(PaymentMethod::class, 'code'),
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
            'items.*.item_package_id' => [
                'nullable',
                Rule::exists('item_packages', 'id'),
            ],
            'items.*.attendees' => 'required|integer|min:1',
        ]);

        $paymentMethod = PaymentMethod::query()
            ->where('code', $request->get('payment_method_code'))
            ->where('status', 1)
            ->firstOrFail();

        $calculation = $this->calculateOrderDetails($request->get('items'), $request->get('coupon_code'));

        DB::beginTransaction();
        try {

            $securityToken = Str::random(40);

            $order = Order::query()->create([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'phone' => $request->get('phone'),
                'payment_method' => $paymentMethod->code,
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

            $responsePayload = [
                'order_id' => $order->id,
                'transaction_token' => $securityToken,
                'sub_total' => $calculation['sub_total'],
                'discount' => $calculation['discount_amount'],
                'total_amount' => $calculation['final_total'],
                'status' => 'pending',
            ];


            if ($paymentMethod->code === 'creditcard') {
                $responsePayload['message'] = 'Redirect to payment gateway';
                $responsePayload['action'] = 'redirect';
                $responsePayload['payment_method'] = 'creditcard';

                $config = $paymentMethod->config;
                $mode = $config['mode'] ?? 'test';
                $paymentBaseUrl = $config[$mode]['url'] ?? null;

                $currencyRate = (float)get_setting('currency_rate', 50);
                $commissionPct = (float)get_setting('payment_commission_percentage', 3);
                $fixedAmount = (float)get_setting('payment_fixed_amount', 3);
                $extraFees = (float)get_setting('payment_extra_fees', 0);

                $amountInEGP = $calculation['final_total'] * $currencyRate;

                $commissionVal = ($amountInEGP * $commissionPct) / 100;
                $extraFeesVal = ($amountInEGP * $extraFees) / 100;
                $total_Amount = $commissionVal + $fixedAmount + $extraFeesVal + $amountInEGP;

                $callbackParams = [
                    'order_id' => $order->id,
                    'transaction_token' => $securityToken,
                    'tenant_id' => $request->header('X-Tenant-ID') ?? $request->get('tenant_id')
                ];

                $gatewayNote = "Order #" . $order->id;

                $data = [
                    'payment_type' => 'MASTERCARD',
                    'invoice_id' => $order->id,
                    'amount' => round($amountInEGP, 2),
                    'total_amount' => round($total_Amount, 2),
                    'total_amount_up_amount' => round(ceil($total_Amount), 2),
                    'commission' => $commissionPct,
                    'fixed_amount' => $fixedAmount,
                    'fees' => $extraFees,
                    'reason' => $gatewayNote,
                    'type' => 'company_integration',
                    'succ' => route('credit.card.success', $callbackParams),
                    'fail' => route('credit.card.cancel', $callbackParams),
                    'title' => getTenantInfo()->name ?? get_setting('site_name_en')
                ];

                if ($paymentBaseUrl) {
                    $gatewayResponse = Http::post($paymentBaseUrl, $data);
                    if ($gatewayResponse->successful()) {
                        $responsePayload['transfer_details'] = $gatewayResponse->body();
                    } else {
                        $responsePayload['transfer_details'] = "Payment Gateway Error";
                    }
                } else {
                    $responsePayload['transfer_details'] = "Payment URL Not Configured";
                }

            } elseif ($paymentMethod->code === 'instapay' || $paymentMethod->code === 'bank_transfer') {
                $responsePayload['message'] = 'Please transfer amount and upload receipt';
                $responsePayload['action'] = 'upload_receipt';
                $responsePayload['payment_method'] = $paymentMethod->code;
                $responsePayload['transfer_details'] = $paymentBaseUrl ?? ($paymentMethod->config ?? '');
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

        if (!$order) {
            return $this->responseMessage(404, 'Order not found');
        }

        if ($request->get('transaction_token') !== $order->transaction_token)
            return $this->responseMessage(400, 'Not Authorized to Upload Receipt for this order');

        if ($order->payment_status === 'paid')
            return $this->responseMessage(400, 'Order is already paid');

        if ($order->payment_status === 'reviewing')
            return $this->responseMessage(400, 'This order is already under reviewed. please wait for review');

        if (!in_array($order->payment_method, ['instapay', 'bank_transfer']))
            return $this->responseMessage(400, 'This payment method does not support receipt upload');

        if ($request->hasFile('receipt')) {
            if ($order->payment_proof) {
                deleteFiles([$order->payment_proof]);
            }

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

            return $this->responseMessage(200, 'Receipt uploaded successfully. Waiting for admin approval.', $order->load('items.item'));
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
            'items.*.item_package_id' => [
                'nullable',
                Rule::exists('item_packages', 'id'),
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
        try {
            $order = Order::query()->findOrFail($request->get('order_id'));

            if ($request->get('transaction_token') !== $order->transaction_token) {
                return redirect()->away(
                    get_setting('payment_failed_url') . '?' . http_build_query([
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
                get_setting('payment_success_url') . '?' . http_build_query([
                    'order_id' => $order->id
                ])
            );

        } catch (\Exception $e) {
            return redirect()->away(
                get_setting('payment_failed_url') . '?' . http_build_query([
                    'order_id' => $request->get('order_id')
                ])
            );
        }
    }

    public function cancel(Request $request): RedirectResponse
    {
        $order = Order::query()->find($request->get('order_id'));
        if ($order && $request->get('transaction_token') !== $order->transaction_token) {
            return redirect()->away(
                get_setting('payment_failed_url') . '?' . http_build_query([
                    'order_id' => $order->id
                ])
            );
        }

        if ($order) {
            $order->update([
                'payment_status' => 'canceled'
            ]);
        }

        return redirect()->away(
            get_setting('payment_cancel_url') . '?' . http_build_query([
                'order_id' => $request->get('order_id')
            ])
        );
    }

    public function getOrderById($id): JsonResponse
    {
        $order = Order::query()->with(['items.item', 'items.itemPackage'])->findOrFail($id);
        return $this->responseMessage(200, 'Order Found', $order);
    }
}
