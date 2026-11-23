<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Requests\LoginRequest;
use Illuminate\Auth\Events\Logout as LogoutEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends \Illuminate\Routing\Controller
{
    public function login(LoginRequest $request): JsonResponse
    {
        if (! Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('api')->plainTextToken;

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return response()->json(['token' => $token, 'user' => $user->load('roles')]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user !== null) {
            $plainTextToken = $request->bearerToken();

            if ($plainTextToken !== null) {
                \Laravel\Sanctum\PersonalAccessToken::findToken($plainTextToken)?->delete();
            }

            if (! Auth::guard('web')->check()) {
                event(new LogoutEvent('sanctum', $user));
            }
        }

        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return response()->json(['message' => 'Logged out.']);
    }
}
