<?php

namespace App\Patterns\Facade\Services;

class TripService
{
    private array $trips = [];

    public function createTrip(array $tripData): array
    {
        $tripId = count($this->trips) + 1;
        $trip = [
            'id' => $tripId,
            'user_id' => $tripData['user_id'],
            'driver_id' => $tripData['driver_id'],
            'pickup_location' => $tripData['pickup_location'],
            'dropoff_location' => $tripData['dropoff_location'],
            'pickup_lat' => $tripData['pickup_lat'],
            'pickup_lng' => $tripData['pickup_lng'],
            'dropoff_lat' => $tripData['dropoff_lat'],
            'dropoff_lng' => $tripData['dropoff_lng'],
            'estimated_fare' => $tripData['estimated_fare'],
            'payment_id' => $tripData['payment_id'],
            'status' => 'confirmed',
            'created_at' => now()->toDateTimeString(),
            'estimated_arrival' => now()->addMinutes(rand(5, 15))->toDateTimeString()
        ];

        $this->trips[$tripId] = $trip;
        return $trip;
    }

    public function getTrip(int $tripId): ?array
    {
        return $this->trips[$tripId] ?? null;
    }

    public function cancelTrip(int $tripId): ?array
    {
        if (isset($this->trips[$tripId])) {
            $this->trips[$tripId]['status'] = 'cancelled';
            $this->trips[$tripId]['cancelled_at'] = now()->toDateTimeString();
            return $this->trips[$tripId];
        }

        return null;
    }

    public function saveRating(array $ratingData): array
    {
        $ratingId = time() . '_' . rand(1000, 9999);

        return [
            'id' => $ratingId,
            'trip_id' => $ratingData['trip_id'],
            'user_id' => $ratingData['user_id'],
            'driver_id' => $ratingData['driver_id'],
            'rating' => $ratingData['rating'],
            'comment' => $ratingData['comment'],
            'created_at' => now()->toDateTimeString()
        ];
    }
}