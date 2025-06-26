<?php

namespace App\Patterns\Facade\Services;

class UserService
{
    public function authenticate(int $userId): ?array
    {
        // Simulate user authentication
        if ($userId > 0) {
            return [
                'id' => $userId,
                'name' => 'User ' . $userId,
                'email' => 'user' . $userId . '@email.com',
                'status' => 'active'
            ];
        }

        return null;
    }
}