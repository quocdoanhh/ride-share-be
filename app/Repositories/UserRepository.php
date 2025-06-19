<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository
{
    public function model(): string
    {
        return User::class;
    }

    /**
     * Find user by phone
     */
    public function firstOrCreateByPhone(string $phone): ?User
    {
        return $this->model->firstOrCreate(['phone' => $phone]);
    }
}
