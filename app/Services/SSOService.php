<?php

namespace App\Services;

use App\Models\User;
use App\Enums\AuthProviderEnum;
use App\Enums\GoogleOAuthEnum;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Exception;

/**
 * Real SSO Service for RideShare Application
 */
class SSOService
{
    const SSO_SESSION_TTL = 3600; // 1 hour
    const SSO_TOKEN_LENGTH = 64;
    const GOOGLE_TOKEN_CACHE_TTL = 300; // 5 minutes

    /**
     * Authenticate with any provider
     */
    public function authenticate(string $provider, array $credentials): ?array
    {
        try {
            if (!AuthProviderEnum::isSupported($provider)) {
                Log::warning("Unsupported authentication provider: {$provider}");
                return null;
            }

            $authProvider = new AuthProviderEnum($provider);

            if ($authProvider->isOAuth()) {
                return $this->authenticateOAuth($provider, $credentials);
            } else {
                return $this->authenticateTraditional($provider, $credentials);
            }
        } catch (Exception $e) {
            Log::error("SSO authentication failed for provider {$provider}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate with OAuth provider (Google)
     */
    private function authenticateOAuth(string $provider, array $credentials): ?array
    {
        if ($provider !== AuthProviderEnum::GOOGLE) {
            Log::warning("OAuth provider not supported: {$provider}");
            return null;
        }

        $oauthToken = $credentials['oauth_token'] ?? null;

        if (!$oauthToken) {
            Log::warning("OAuth token not provided for Google authentication");
            return null;
        }

        // Validate token format
        if (!GoogleOAuthEnum::isValidTokenFormat($oauthToken)) {
            Log::warning("Invalid OAuth token format provided");
            return null;
        }

        // Get user info from Google
        $googleUserData = $this->getGoogleUserInfo($oauthToken);

        if (!$googleUserData) {
            Log::error("Failed to get user info from Google OAuth");
            return null;
        }

        // Validate email verification
        if (!($googleUserData['verified_email'] ?? false)) {
            Log::warning("Google account email not verified: {$googleUserData['email']}");
            return null;
        }

        $user = $this->createOrGetGoogleUser($googleUserData);

        if (!$user) {
            Log::error("Failed to create or get user for Google ID: {$googleUserData['id']}");
            return null;
        }

        return $this->createSSOSession($user, $provider);
    }

    /**
     * Get user information from Google OAuth
     */
    private function getGoogleUserInfo(string $oauthToken): ?array
    {
        try {
            $cacheKey = "google_user_info_" . md5($oauthToken);

            // Check cache first
            $cachedData = Cache::get($cacheKey);
            if ($cachedData) {
                return $cachedData;
            }

            $response = Http::withHeaders([
                'Authorization' => "Bearer {$oauthToken}",
                'Accept' => 'application/json'
            ])->get(GoogleOAuthEnum::USER_INFO_URL);

            if (!$response->successful()) {
                Log::error("Google API request failed: " . $response->body());
                return null;
            }

            $userData = $response->json();

            // Validate required fields
            $requiredFields = ['id', 'email', 'name'];
            foreach ($requiredFields as $field) {
                if (empty($userData[$field])) {
                    Log::error("Missing required field in Google user data: {$field}");
                    return null;
                }
            }

            // Cache the result
            Cache::put($cacheKey, $userData, self::GOOGLE_TOKEN_CACHE_TTL);

            return $userData;
        } catch (Exception $e) {
            Log::error("Exception while getting Google user info: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Authenticate with traditional provider (Phone)
     */
    private function authenticateTraditional(string $provider, array $credentials): ?array
    {
        if ($provider !== AuthProviderEnum::PHONE) {
            Log::warning("Traditional provider not supported: {$provider}");
            return null;
        }

        return $this->authenticateWithPhone($credentials);
    }

    /**
     * Authenticate with phone
     */
    private function authenticateWithPhone(array $credentials): ?array
    {
        $phone = $credentials['phone'] ?? null;
        $code = $credentials['code'] ?? null;

        if (!$phone) {
            Log::warning("Phone number not provided for authentication");
            return null;
        }

        // Validate phone number format
        if (!preg_match('/^\+[1-9]\d{1,14}$/', $phone)) {
            Log::warning("Invalid phone number format: {$phone}");
            return null;
        }

        if ($code) {
            $user = $this->verifyPhoneLogin($phone, $code);
        } else {
            $user = $this->createOrGetUserByPhone($phone);
        }

        if (!$user) {
            Log::warning("Phone authentication failed for: {$phone}");
            return null;
        }

        return $this->createSSOSession($user, AuthProviderEnum::PHONE);
    }

    /**
     * Create or get Google user
     */
    private function createOrGetGoogleUser(array $googleUserData): ?User
    {
        try {
            $user = User::where('google_id', $googleUserData['id'])->first();

            if (!$user) {
                // Check if email already exists
                $existingUser = User::where('email', $googleUserData['email'])->first();

                if ($existingUser) {
                    // Link existing user to Google
                    $existingUser->update([
                        'google_id' => $googleUserData['id'],
                        'provider' => 'google',
                        'avatar' => $googleUserData['picture'] ?? null,
                        'email_verified_at' => $googleUserData['verified_email'] ? now() : null
                    ]);
                    $user = $existingUser;
                } else {
                    // Create new user
                    $user = User::create([
                        'name' => $googleUserData['name'],
                        'email' => $googleUserData['email'],
                        'google_id' => $googleUserData['id'],
                        'avatar' => $googleUserData['picture'] ?? null,
                        'provider' => 'google',
                        'email_verified_at' => $googleUserData['verified_email'] ? now() : null
                    ]);
                }

                Log::info("Created/linked Google user: {$user->email}");
            }

            return $user;
        } catch (Exception $e) {
            Log::error("Failed to create/get Google user: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify phone login with code
     */
    private function verifyPhoneLogin(string $phone, string $code): ?User
    {
        $user = User::where('phone', $phone)
                  ->where('login_verification_code', $code)
                  ->first();

        if ($user) {
            // Clear verification code after successful login
            $user->update(['login_verification_code' => null]);
            Log::info("Phone verification successful for: {$phone}");
        }

        return $user;
    }

    /**
     * Create or get user by phone
     */
    private function createOrGetUserByPhone(string $phone): ?User
    {
        try {
            $user = User::firstOrCreate(
                ['phone' => $phone],
                [
                    'name' => 'User ' . substr($phone, -4),
                    'provider' => 'phone'
                ]
            );

            Log::info("Created/found user by phone: {$phone}");
            return $user;
        } catch (Exception $e) {
            Log::error("Failed to create/get user by phone: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Create SSO session
     */
    private function createSSOSession(User $user, string $provider): array
    {
        try {
            $ssoToken = $this->generateSSOToken();
            $sessionData = [
                'user_id' => $user->id,
                'provider' => $provider,
                'created_at' => now()->timestamp,
                'expires_at' => now()->addSeconds(self::SSO_SESSION_TTL)->timestamp,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ];

            Cache::put($this->getSSOCacheKey($ssoToken), $sessionData, self::SSO_SESSION_TTL);

            $apiToken = $user->createToken("sso_{$provider}_token")->plainTextToken;

            Log::info("SSO session created for user: {$user->id} with provider: {$provider}");

            return [
                'sso_token' => $ssoToken,
                'api_token' => $apiToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'provider' => $provider,
                    'provider_display' => (new AuthProviderEnum($provider))->getDisplayName(),
                    'avatar' => $user->avatar,
                    'email_verified_at' => $user->email_verified_at
                ],
                'session_expires_at' => $sessionData['expires_at'],
                'type' => 'sso'
            ];
        } catch (Exception $e) {
            Log::error("Failed to create SSO session: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get SSO session info
     */
    public function getSSOSessionInfo(string $ssoToken): ?array
    {
        try {
            $sessionData = Cache::get($this->getSSOCacheKey($ssoToken));

            if (!$sessionData) {
                Log::warning("SSO session not found for token: " . substr($ssoToken, 0, 10) . "...");
                return null;
            }

            if ($sessionData['expires_at'] < now()->timestamp) {
                Cache::forget($this->getSSOCacheKey($ssoToken));
                Log::info("SSO session expired for user: {$sessionData['user_id']}");
                return null;
            }

            $user = User::find($sessionData['user_id']);

            if (!$user) {
                Log::error("User not found for SSO session: {$sessionData['user_id']}");
                Cache::forget($this->getSSOCacheKey($ssoToken));
                return null;
            }

            return [
                'session' => $sessionData,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone,
                    'provider' => $sessionData['provider'],
                    'provider_display' => (new AuthProviderEnum($sessionData['provider']))->getDisplayName(),
                    'avatar' => $user->avatar,
                    'email_verified_at' => $user->email_verified_at
                ],
                'is_active' => true,
                'expires_in' => $sessionData['expires_at'] - now()->timestamp
            ];
        } catch (Exception $e) {
            Log::error("Failed to get SSO session info: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Generate SSO token
     */
    private function generateSSOToken(): string
    {
        return Str::random(self::SSO_TOKEN_LENGTH);
    }

    /**
     * Get cache key
     */
    private function getSSOCacheKey(string $ssoToken): string
    {
        return "sso_session_{$ssoToken}";
    }

    /**
     * Revoke SSO session
     */
    public function revokeSSOSession(string $ssoToken): bool
    {
        try {
            $sessionData = Cache::get($this->getSSOCacheKey($ssoToken));

            if ($sessionData) {
                Cache::forget($this->getSSOCacheKey($ssoToken));
                Log::info("SSO session revoked for user: {$sessionData['user_id']}");
                return true;
            }

            return false;
        } catch (Exception $e) {
            Log::error("Failed to revoke SSO session: " . $e->getMessage());
            return false;
        }
    }
}