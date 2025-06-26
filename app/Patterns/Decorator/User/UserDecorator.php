<?php

namespace App\Patterns\Decorator\User;

/**
 * Abstract Decorator - Base class cho tất cả user decorators
 */
abstract class UserDecorator implements UserInterface
{
    protected UserInterface $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function getInfo(): array
    {
        return $this->user->getInfo();
    }

    public function getPermissions(): array
    {
        return $this->user->getPermissions();
    }

    public function canAccess(string $resource): bool
    {
        return $this->user->canAccess($resource);
    }

    public function getDisplayName(): string
    {
        return $this->user->getDisplayName();
    }
}

/**
 * Concrete Decorator - Driver role
 */
class DriverDecorator extends UserDecorator
{
    private array $driverPermissions = [
        'trip:create',
        'trip:accept',
        'trip:complete',
        'location:update',
        'earnings:view'
    ];

    public function getInfo(): array
    {
        $info = parent::getInfo();
        $info['type'] = 'driver';
        $info['role'] = 'Tài xế';
        $info['permissions'] = array_merge($info['permissions'], $this->driverPermissions);
        return $info;
    }

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), $this->driverPermissions);
    }

    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->driverPermissions) || parent::canAccess($resource);
    }

    public function getDisplayName(): string
    {
        return parent::getDisplayName() . ' (Tài xế)';
    }
}

/**
 * Concrete Decorator - Passenger role
 */
class PassengerDecorator extends UserDecorator
{
    private array $passengerPermissions = [
        'trip:request',
        'trip:rate',
        'payment:manage',
        'history:view'
    ];

    public function getInfo(): array
    {
        $info = parent::getInfo();
        $info['type'] = 'passenger';
        $info['role'] = 'Hành khách';
        $info['permissions'] = array_merge($info['permissions'], $this->passengerPermissions);
        return $info;
    }

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), $this->passengerPermissions);
    }

    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->passengerPermissions) || parent::canAccess($resource);
    }

    public function getDisplayName(): string
    {
        return parent::getDisplayName() . ' (Hành khách)';
    }
}

/**
 * Concrete Decorator - Admin role
 */
class AdminDecorator extends UserDecorator
{
    private array $adminPermissions = [
        'user:manage',
        'trip:monitor',
        'system:config',
        'reports:view',
        'support:manage'
    ];

    public function getInfo(): array
    {
        $info = parent::getInfo();
        $info['type'] = 'admin';
        $info['role'] = 'Quản trị viên';
        $info['permissions'] = array_merge($info['permissions'], $this->adminPermissions);
        return $info;
    }

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), $this->adminPermissions);
    }

    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->adminPermissions) || parent::canAccess($resource);
    }

    public function getDisplayName(): string
    {
        return parent::getDisplayName() . ' (Admin)';
    }
}

/**
 * Concrete Decorator - Premium user
 */
class PremiumDecorator extends UserDecorator
{
    private array $premiumPermissions = [
        'priority:booking',
        'premium:support',
        'discount:apply',
        'premium:features'
    ];

    public function getInfo(): array
    {
        $info = parent::getInfo();
        $info['subscription'] = 'premium';
        $info['permissions'] = array_merge($info['permissions'], $this->premiumPermissions);
        return $info;
    }

    public function getPermissions(): array
    {
        return array_merge(parent::getPermissions(), $this->premiumPermissions);
    }

    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->premiumPermissions) || parent::canAccess($resource);
    }

    public function getDisplayName(): string
    {
        return '⭐ ' . parent::getDisplayName();
    }
}

/**
 * Concrete Decorator - Verified user
 */
class VerifiedDecorator extends UserDecorator
{
    public function getInfo(): array
    {
        $info = parent::getInfo();
        $info['verified'] = true;
        $info['verification_date'] = now()->toDateString();
        return $info;
    }

    public function getDisplayName(): string
    {
        return parent::getDisplayName() . ' ✓';
    }
}