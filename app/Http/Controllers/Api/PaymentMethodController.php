<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Traits\ResponseTrait;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $paymentMethods = PaymentMethod::query()->where('status', 1)->get();
        return $this->responseMessage(200, 'Payment Methods', PaymentMethodResource::collection($paymentMethods));
    }

}
