<?php

namespace App\Patterns\Facade;

use App\Patterns\Facade\Services\UserService;
use App\Patterns\Facade\Services\PaymentService;
use App\Patterns\Facade\Services\DriverService;
use App\Patterns\Facade\Services\NotificationService;
use App\Patterns\Facade\Services\TripService;

class TripBookingFacade
{
    private UserService $userService;
    private PaymentService $paymentService;
    private DriverService $driverService;
    private NotificationService $notificationService;
    private TripService $tripService;

    public function __construct()
    {
        $this->userService = new UserService();
        $this->paymentService = new PaymentService();
        $this->driverService = new DriverService();
        $this->notificationService = new NotificationService();
        $this->tripService = new TripService();
    }

    /**
     * Book trip - a simple method instead of calling multiple services
     * @param array $bookingData
     *
     * @throws \Exception
     * @return array
     */
    public function bookTrip(array $bookingData): array
    {
        try {
            // 1. Authenticate user
            $user = $this->userService->authenticate($bookingData['user_id']);
            if (!$user) {
                throw new \Exception('Invalid user');
            }

            // 2. Check payment
            $payment = $this->paymentService->processPayment([
                'user_id' => $user['id'],
                'amount' => $bookingData['estimated_fare'],
                'payment_method' => $bookingData['payment_method']
            ]);

            if (!$payment['success']) {
                throw new \Exception('Payment failed: ' . $payment['message']);
            }

            // 3. Find nearest driver
            $driver = $this->driverService->findNearestDriver([
                'pickup_lat' => $bookingData['pickup_lat'],
                'pickup_lng' => $bookingData['pickup_lng'],
                'vehicle_type' => $bookingData['vehicle_type'] ?? 'car'
            ]);

            if (!$driver) {
                throw new \Exception('No suitable driver found');
            }

            // 4. Create trip
            $trip = $this->tripService->createTrip([
                'user_id' => $user['id'],
                'driver_id' => $driver['id'],
                'pickup_location' => $bookingData['pickup_location'],
                'dropoff_location' => $bookingData['dropoff_location'],
                'pickup_lat' => $bookingData['pickup_lat'],
                'pickup_lng' => $bookingData['pickup_lng'],
                'dropoff_lat' => $bookingData['dropoff_lat'],
                'dropoff_lng' => $bookingData['dropoff_lng'],
                'estimated_fare' => $bookingData['estimated_fare'],
                'payment_id' => $payment['payment_id']
            ]);

            // 5. Send booking confirmation
            $this->notificationService->sendBookingConfirmation([
                'user_id' => $user['id'],
                'driver_id' => $driver['id'],
                'trip_id' => $trip['id'],
                'pickup_location' => $bookingData['pickup_location'],
                'estimated_arrival' => $trip['estimated_arrival']
            ]);

            return [
                'success' => true,
                'trip_id' => $trip['id'],
                'driver' => $driver,
                'estimated_arrival' => $trip['estimated_arrival'],
                'message' => 'Trip booked successfully!'
            ];

        } catch (\Exception $e) {
            // Rollback if needed
            $this->rollbackBooking($bookingData);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Cancel trip
     * @param int $tripId
     * @param int $userId
     *
     * @return array
     * @throws \Exception
     */
    public function cancelTrip(int $tripId, int $userId): array
    {
        try {
            // 1. Authenticate user
            $user = $this->userService->authenticate($userId);
            if (!$user) {
                throw new \Exception('Invalid user');
            }

            // 2. Get trip information
            $trip = $this->tripService->getTrip($tripId);
            if (!$trip || $trip['user_id'] != $userId) {
                throw new \Exception('Trip not found or not belong to you');
            }

            // 3. Cancel trip
            $cancelledTrip = $this->tripService->cancelTrip($tripId);

            // 4. Refund if needed
            if ($trip['status'] === 'confirmed') {
                $refund = $this->paymentService->processRefund($trip['payment_id']);
            }

            // 5. Send cancellation notification
            $this->notificationService->sendCancellationNotification([
                'user_id' => $userId,
                'driver_id' => $trip['driver_id'],
                'trip_id' => $tripId
            ]);

            return [
                'success' => true,
                'message' => 'Trip cancelled successfully',
                'refund_amount' => $refund['amount'] ?? 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Track trip
     * @param int $tripId
     *
     * @return array
     * @throws \Exception
     */
    public function trackTrip(int $tripId): array
    {
        $trip = $this->tripService->getTrip($tripId);
        if (!$trip) {
            return ['success' => false, 'message' => 'Trip not found'];
        }

        $driver = $this->driverService->getDriver($trip['driver_id']);
        $location = $this->driverService->getDriverLocation($trip['driver_id']);

        return [
            'success' => true,
            'trip' => $trip,
            'driver' => $driver,
            'current_location' => $location,
            'estimated_arrival' => $trip['estimated_arrival']
        ];
    }

    /**
     * Rate trip
     * @param int $tripId
     * @param int $userId
     * @param int $rating
     * @param string $comment
     *
     * @return array
     * @throws \Exception
     */
    public function rateTrip(int $tripId, int $userId, int $rating, string $comment = ''): array
    {
        try {
            // 1. Authenticate user
            $user = $this->userService->authenticate($userId);
            if (!$user) {
                throw new \Exception('Invalid user');
            }

            // 2. Check if trip is completed
            $trip = $this->tripService->getTrip($tripId);
            if (!$trip || $trip['status'] !== 'completed') {
                throw new \Exception('Trip not completed');
            }

            // 3. Save rating
            $ratingData = $this->tripService->saveRating([
                'trip_id' => $tripId,
                'user_id' => $userId,
                'driver_id' => $trip['driver_id'],
                'rating' => $rating,
                'comment' => $comment
            ]);

            // 4. Send rating notification to driver
            $this->notificationService->sendRatingNotification([
                'driver_id' => $trip['driver_id'],
                'rating' => $rating,
                'comment' => $comment
            ]);

            return [
                'success' => true,
                'message' => 'Rating saved successfully',
                'rating_id' => $ratingData['id']
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Rollback if error
     * @param array $bookingData
     *
     * @return void
     */
    private function rollbackBooking(array $bookingData): void
    {
        // Logic rollback if needed
        // Example: refund, cancel trip, etc.
    }
}