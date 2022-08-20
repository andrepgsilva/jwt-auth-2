<?php

namespace App\Http\Controllers;

use App\Custom\Jwt\Config;
use App\Custom\Jwt\Issuer;
use App\Models\TokenBlacklist;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('email', 'password');

        $user = User::where('email', $credentials['email'])->first();
        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized',
            ], 401);
        }

        [$token, $refreshToken] = (new Issuer())->getTokenPair(true);

        $user = ['name' => $user['name'],'email' => $user['email']];

        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'type' => 'bearer',
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user = ['name' => $user->name, 'email' => $user->email];

        [$token, $refreshToken] = (new Issuer())->getTokenPair(true);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'type' => 'bearer',
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        try {
            $token = explode('Bearer ', $request->header('authorization'))[1];

            TokenBlacklist::create(['token' => $token]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token invalid or malformed',
            ], 400);
        } finally {
            return response()->json([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);
        }
    }

    public function refresh(Request $request): JsonResponse
    {
        try {
            $refreshToken = $request->header('authorization');
            $refreshToken = explode('Bearer ', $refreshToken)[1];
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token invalid or malformed',
            ], 400);
        }

        $refreshTokenObject = Config::get()->parser()->parse(
            $refreshToken
        );

        $isRefreshTokenExpired = $refreshTokenObject->isExpired(now());

        if ($isRefreshTokenExpired) {
            return response()->json([
                'message' => 'Refresh token expired',
            ], 401);
        }

        [$token, $refreshToken] = (new Issuer())->getTokenPair(true);

        return response()->json([
            'status' => 'success',
            'authorization' => [
                'token' => $token,
                'refresh_token' => $refreshToken,
                'type' => 'bearer',
            ],
        ]);
    }
}
