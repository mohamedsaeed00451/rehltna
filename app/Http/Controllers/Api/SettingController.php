<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;

class SettingController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $fileKeys = [
            'main_logo_dark',
            'main_logo_light',
            'favicon',
            'company_profile_en',
            'company_profile_ar'
        ];

        $settings = Setting::all()->mapWithKeys(function ($setting) use ($fileKeys) {
            $value = $setting->value;

            if (in_array($setting->key, $fileKeys) && $value) {
                $value = asset($value);
            }

            return [$setting->key => $value];
        });

        return $this->responseMessage(200, 'Settings', $settings);
    }

}
