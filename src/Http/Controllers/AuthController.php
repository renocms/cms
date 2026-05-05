<?php

namespace Reno\Cms\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Reno\Cms\Http\Requests\Auth\LoginRequest;
use Reno\Cms\Interfaces\Services\PermissionServiceInterface;

class AuthController extends Controller
{
    public function __construct(
        protected PermissionServiceInterface $permissionService,
    )
    {
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user instanceof User || !$this->permissionService->hasPermission($user, 'admin.view')) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return response()->json([
                    'message' => __('cms::cms.insufficient_permissions_admin_access'),
                ], 403);
            }

            return response()->json([
                'user' => $user,
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

