<?php

namespace App\Services;

use App\Repositories\DriverRepository;

class DriverService
{
    public function __construct(private DriverRepository $driverRepository)
    {
    }

    public function updateOrCreateDriver(array $data)
    {
        $user = auth()->user();
        $this->driverRepository->updateOrCreateDriver($data);

        return $user->load('driver');
    }
}