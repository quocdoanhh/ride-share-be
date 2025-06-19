<?php

namespace App\Services;

use App\Repositories\TripRepository;

class TripService
{
    public function __construct(private TripRepository $tripRepository)
    {
    }

    public function createTrip(array $data)
    {
        return $this->tripRepository->create([
            'user_id' => auth()->user()->id,
            ...$data,
        ]);
    }

    public function getTrip(int $id)
    {
        $trip = $this->tripRepository->findWith($id, ['user', 'driver']);

        if ($trip->user_id === auth()->user()->id) {
            return $trip;
        }

        if ($trip->driver
            && auth()->user()->driver
            && $trip->driver->user_id === auth()->user()->id)
        {
            return $trip;
        }

        return null;
    }

    public function acceptTrip(int $id, array $data)
    {
        $trip = $this->tripRepository->findOrFail($id);
        $trip->update([
            'driver_id' => auth()->user()->id,
            ...$data,
        ]);

        return $trip->load('driver.user');
    }

    public function startTrip(int $id)
    {
        $trip = $this->tripRepository->findOrFail($id);
        $trip->update([
            'is_started' => true,
        ]);

        return $trip->load('driver.user');
    }

    public function endTrip(int $id)
    {
        $trip = $this->tripRepository->findOrFail($id);
        $trip->update([
            'is_completed' => true,
        ]);

        return $trip->load('driver.user');
    }

    public function locationTrip(int $id, array $data)
    {
        $trip = $this->tripRepository->findOrFail($id);
        $trip->update($data);

        return $trip->load('driver.user');
    }
}