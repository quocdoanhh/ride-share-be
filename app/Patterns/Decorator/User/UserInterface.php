<?php

namespace App\Patterns\Decorator\User;

/**
 * Interface User - Component interface
 */
interface UserInterface
{
    public function getInfo(): array;
    public function getPermissions(): array;
    public function canAccess(string $resource): bool;
    public function getDisplayName(): string;
}

/**
 * Concrete Component - Basic User
 */
class BasicUser implements UserInterface
{
    protected string $name;
    protected string $email;
    protected array $basicPermissions;

    public function __construct(string $name, string $email)
    {
        $this->name = $name;
        $this->email = $email;
        $this->basicPermissions = ['profile:read', 'profile:update'];
    }

    public function getInfo(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'type' => 'basic_user',
            'permissions' => $this->basicPermissions
        ];
    }

    public function getPermissions(): array
    {
        return $this->basicPermissions;
    }

    public function canAccess(string $resource): bool
    {
        return in_array($resource, $this->basicPermissions);
    }

    public function getDisplayName(): string
    {
        return $this->name;
    }
}