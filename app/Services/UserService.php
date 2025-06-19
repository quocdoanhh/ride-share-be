<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(private UserRepository $userRepository)
    {
    }

    public function updateUser(array $data)
    {
        return $this->userRepository->update(auth()->user()->id, $data);
    }
}