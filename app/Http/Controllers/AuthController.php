<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Notifications\LoginVerification;
use App\Http\Requests\VerifyLoginRequest;
use App\Services\AuthService;
use App\Services\SSOService;
use App\Enums\AuthProviderEnum;
use App\Enums\GoogleOAuthEnum;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private SSOService $ssoService
    ) {
    }

    // Traditional phone authentication
    public function login(LoginRequest $request)
    {
        $user = $this->authService->login($request->validated());
        if (!$user) {
            return response()->json([
                'message' => 'Could not process a user with that phone number',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $code = generate_random_string();
        $user->update(['login_verification_code' => $code]);

        return response()->json([
            'message' => 'Verification code sent to your phone',
        ]);
    }

    public function verify(VerifyLoginRequest $request)
    {
        $user = $this->authService->verifyLogin($request->validated());

        if (!$user) {
            return response()->json([
                'message' => 'Invalid verification code',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->update(['login_verification_code' => null]);

        return response()->json([
            'token' => $user->createToken('phone_verification')->plainTextToken
        ]);
    }



    public function getVerificationCode(LoginRequest $request)
    {
        $user = $this->authService->login($request->validated());
        if (!$user) {
            return response()->json([
                'message' => 'User not found',
            ], Response::HTTP_NOT_FOUND);
        }

        return response()->json([
            'code' => $user->login_verification_code,
        ]);
    }



    public function authenticateWithPhone(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'code' => 'nullable|string'
        ]);

        $credentials = [
            'phone' => $request->input('phone'),
            'code' => $request->input('code')
        ];

        $result = $this->ssoService->authenticate(AuthProviderEnum::PHONE, $credentials);

        if (!$result) {
            return response()->json([
                'message' => 'Phone authentication failed',
                'error' => 'Invalid phone number or verification code'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => 'Phone authentication success',
            'data' => $result
        ]);
    }

    public function authenticateWithGoogle(Request $request)
    {
        $request->validate([
            'oauth_token' => 'required|string'
        ]);

        $oauthToken = $request->input('oauth_token');

        // Validate Google OAuth configuration
        if (!GoogleOAuthEnum::isConfigured()) {
            return response()->json([
                'message' => 'Google OAuth is not configured',
                'error' => 'Please configure Google OAuth credentials'
            ], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        // Validate token format
        if (!GoogleOAuthEnum::isValidTokenFormat($oauthToken)) {
            return response()->json([
                'message' => 'Invalid OAuth token format',
                'error' => 'Token must be at least 20 characters long'
            ], Response::HTTP_BAD_REQUEST);
        }

        $credentials = [
            'oauth_token' => $oauthToken
        ];

        $result = $this->ssoService->authenticate(AuthProviderEnum::GOOGLE, $credentials);

        if (!$result) {
            return response()->json([
                'message' => 'Google OAuth authentication failed',
                'error' => 'Invalid OAuth token'
            ], Response::HTTP_UNAUTHORIZED);
        }

        return response()->json([
            'message' => GoogleOAuthEnum::getSuccessMessage(),
            'data' => $result
        ]);
    }


}
