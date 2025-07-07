<?php

namespace App\Enums;

use BenSampo\Enum\Enum;

/**
 * Authentication Provider Enum
 */
final class AuthProviderEnum extends Enum
{
    const PHONE = 'phone';
    const GOOGLE = 'google';

    /**
     * Get display name
     */
    public function getDisplayName(): string
    {
        return match($this->value) {
            self::PHONE => 'Phone',
            self::GOOGLE => 'Google',
            default => ucfirst($this->value)
        };
    }

    /**
     * Check if provider is OAuth
     */
    public function isOAuth(): bool
    {
        return $this->value === self::GOOGLE;
    }

    /**
     * Check if provider is supported
     */
    public static function isSupported(string $provider): bool
    {
        return in_array($provider, self::getValues());
    }
}