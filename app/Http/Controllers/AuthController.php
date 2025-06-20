<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Notifications\LoginVerification;
use App\Http\Requests\VerifyLoginRequest;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Controller;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function __construct(private AuthService $userService)
    {
    }

    public function login(LoginRequest $request)
    {
        $user = $this->userService->login($request->validated());
        if (!$user) {
            return response()->json([
                'message' => 'Could not process a user with that phone number',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->notify(new LoginVerification());

        return response()->json([
            'message' => 'Verification code sent to your phone',
        ]);
    }

    public function verify(VerifyLoginRequest $request)
    {
        $user = $this->userService->verifyLogin($request->validated());

        if (!$user) {
            return response()->json([
                'message' => 'Invalid verification code',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->update(['login_verification_code' => null]);

        return response()->json([
            'token' => $user->createToken($request->code)->plainTextToken
        ]);
    }

    public function getMe()
    {
        return response()->json([
            'data' => auth()->user(),
        ]);
    }
}
