<?php

namespace App\Patterns\Facade\Services;

class DriverService
{
    public function findNearestDriver(array $locationData): ?array
    {
        // Simulate finding nearest driver
        $drivers = [
            [
                'id' => 1,
                'name' => 'Tài xế Nguyễn Văn A',
                'phone' => '0901234567',
                'vehicle_type' => 'car',
                'rating' => 4.8,
                'distance' => 0.5
            ],
            [
                'id' => 2,
                'name' => 'Tài xế Trần Thị B',
                'phone' => '0901234568',
                'vehicle_type' => 'car',
                'rating' => 4.6,
                'distance' => 1.2
            ]
        ];

        // Find driver with matching vehicle type
        foreach ($drivers as $driver) {
            if ($driver['vehicle_type'] === $locationData['vehicle_type']) {
                return $driver;
            }
        }

        return null;
    }

    public function getDriver(int $driverId): ?array
    {
        // Simulate getting driver info
        return [
            'id' => $driverId,
            'name' => 'Tài xế ' . $driverId,
            'phone' => '090123456' . $driverId,
            'vehicle_type' => 'car',
            'rating' => 4.5 + (rand(0, 5) / 10),
            'status' => 'available'
        ];
    }

    public function getDriverLocation(int $driverId): array
    {
        // Simulate getting driver location
        return [
            'lat' => 10.762622 + (rand(-100, 100) / 10000),
            'lng' => 106.660172 + (rand(-100, 100) / 10000),
            'timestamp' => time()
        ];
    }
}