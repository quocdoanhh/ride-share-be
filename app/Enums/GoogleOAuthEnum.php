<?php

namespace App\Enums;

use BenSampo\Enum\Enum;
use Illuminate\Support\Str;

/**
 * Google OAuth Enum - Real Implementation
 */
final class GoogleOAuthEnum extends Enum
{
    const AUTH_URL = 'https://accounts.google.com/o/oauth2/auth';
    const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    const USER_INFO_URL = 'https://www.googleapis.com/oauth2/v2/userinfo';
    const REVOKE_URL = 'https://oauth2.googleapis.com/revoke';

    /**
     * Get required scopes for RideShare app
     */
    public static function getRequiredScopes(): array
    {
        return [
            'openid',
            'email',
            'profile',
            'https://www.googleapis.com/auth/user.addresses.read',
            'https://www.googleapis.com/auth/user.phonenumbers.read'
        ];
    }

    /**
     * Get scopes string for OAuth request
     */
    public static function getScopesString(): string
    {
        return implode(' ', self::getRequiredScopes());
    }

    /**
     * Check if Google OAuth is properly configured
     */
    public static function isConfigured(): bool
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        return !empty($clientId) && !empty($clientSecret) && !empty($redirectUri);
    }

    /**
     * Validate OAuth token format (ID token or access token)
     */
    public static function isValidTokenFormat(string $token): bool
    {
        // JWT tokens are typically 3 parts separated by dots
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            return false;
        }

        // Each part should be base64 encoded
        foreach ($parts as $part) {
            if (!preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $part)) {
                return false;
            }
        }

        return strlen($token) > 100; // JWT tokens are typically long
    }

    /**
     * Get OAuth configuration for RideShare
     */
    public static function getOAuthConfig(): array
    {
        return [
            'client_id' => config('services.google.client_id'),
            'client_secret' => config('services.google.client_secret'),
            'redirect_uri' => config('services.google.redirect'),
            'auth_url' => self::AUTH_URL,
            'token_url' => self::TOKEN_URL,
            'user_info_url' => self::USER_INFO_URL,
            'scopes' => self::getRequiredScopes(),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
    }

    /**
     * Get error messages for different scenarios
     */
    public static function getErrorMessage(string $type): string
    {
        return match($type) {
            'not_configured' => 'Google OAuth is not configured. Please contact administrator.',
            'invalid_token' => 'Invalid OAuth token provided.',
            'token_expired' => 'OAuth token has expired. Please login again.',
            'insufficient_scope' => 'Insufficient permissions. Please grant required scopes.',
            'user_not_found' => 'Google user not found or account not linked.',
            'verification_failed' => 'Email verification failed. Please use verified Google account.',
            default => 'Google OAuth authentication failed.'
        };
    }

    /**
     * Get success messages
     */
    public static function getSuccessMessage(string $action = 'login'): string
    {
        return match($action) {
            'login' => 'Successfully authenticated with Google',
            'link' => 'Google account linked successfully',
            'unlink' => 'Google account unlinked successfully',
            default => 'Google OAuth operation completed successfully'
        };
    }

    /**
     * Get OAuth state for CSRF protection
     */
    public static function generateOAuthState(): string
    {
        return Str::random(40);
    }

    /**
     * Validate OAuth state
     */
    public static function validateOAuthState(string $state, string $storedState): bool
    {
        return hash_equals($storedState, $state);
    }

    /**
     * Get user data structure expected from Google
     */
    public static function getExpectedUserData(): array
    {
        return [
            'id' => 'string', // Google user ID
            'email' => 'string', // User email
            'verified_email' => 'boolean', // Email verification status
            'name' => 'string', // Full name
            'given_name' => 'string', // First name
            'family_name' => 'string', // Last name
            'picture' => 'string', // Profile picture URL
            'locale' => 'string', // User locale
            'hd' => 'string' // Hosted domain (for G Suite)
        ];
    }

    /**
     * Get RideShare specific scopes explanation
     */
    public static function getScopesExplanation(): array
    {
        return [
            'openid' => 'OpenID Connect for secure authentication',
            'email' => 'Access to user email address',
            'profile' => 'Access to basic profile information',
            'https://www.googleapis.com/auth/user.addresses.read' => 'Access to user addresses for ride pickup/dropoff',
            'https://www.googleapis.com/auth/user.phonenumbers.read' => 'Access to user phone numbers for ride coordination'
        ];
    }
}