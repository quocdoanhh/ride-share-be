<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function login(array $data)
    {
        return $this->userRepository->firstOrCreateByPhone($data['phone']);
    }

    public function verifyLogin(array $data)
    {
        return $this->userRepository->whereFirst([
            'phone' => $data['phone'],
            'login_verification_code' => $data['code'],
        ]);
    }
}