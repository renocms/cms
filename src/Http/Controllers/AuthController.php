<?php

namespace Reno\Cms\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Reno\Cms\Http\Requests\Auth\LoginRequest;

class AuthController extends Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return response()->json([
                'user' => Auth::user(),
                'message' => __('cms::cms.login_success'),
            ]);
        }

        return response()->json([
            'message' => __('cms::cms.invalid_credentials'),
        ], 401);
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => __('cms::cms.logout_success'),
        ]);
    }

    public function user(): JsonResponse
    {
        return response()->json([
            'user' => Auth::user(),
        ]);
    }
}

