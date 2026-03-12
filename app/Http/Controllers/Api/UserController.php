<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ResponseTrait;
use App\Models\ResidencyUser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use ResponseTrait;

    public function index(): JsonResponse
    {
        $users = ResidencyUser::query()->select('name', 'phone')->get();
        return $this->responseMessage(200, 'success', $users);
    }
}
