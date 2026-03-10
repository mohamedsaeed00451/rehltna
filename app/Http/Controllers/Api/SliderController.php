<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Slider;
use Illuminate\Http\JsonResponse;

class SliderController extends Controller
{
    use ResponseTrait;

    public function getSliders(): JsonResponse
    {
        $sliders = Slider::query()->where('status', 1)->orderBy('order', 'asc')->get();
        return $this->responseMessage(200, 'success', $sliders);
    }
}
