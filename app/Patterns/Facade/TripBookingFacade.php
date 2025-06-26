<?php

namespace App\Patterns\Facade;

use App\Patterns\Facade\Services\UserService;
use App\Patterns\Facade\Services\PaymentService;
use App\Patterns\Facade\Services\DriverService;
use App\Patterns\Facade\Services\NotificationService;
use App\Patterns\Facade\Services\TripService;

/**
 * Facade Pattern - Đơn giản hóa việc đặt chuyến đi
 *
 * Facade cung cấp interface đơn giản cho các subsystem phức tạp:
 * - UserService: Xác thực user
 * - PaymentService: Xử lý thanh toán
 * - DriverService: Tìm tài xế
 * - NotificationService: Gửi thông báo
 * - TripService: Tạo chuyến đi
 */
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
     * Đặt chuyến đi - một method đơn giản thay vì gọi nhiều service
     */
    public function bookTrip(array $bookingData): array
    {
        try {
            // 1. Xác thực user
            $user = $this->userService->authenticate($bookingData['user_id']);
            if (!$user) {
                throw new \Exception('User không hợp lệ');
            }

            // 2. Kiểm tra thanh toán
            $payment = $this->paymentService->processPayment([
                'user_id' => $user['id'],
                'amount' => $bookingData['estimated_fare'],
                'payment_method' => $bookingData['payment_method']
            ]);

            if (!$payment['success']) {
                throw new \Exception('Thanh toán thất bại: ' . $payment['message']);
            }

            // 3. Tìm tài xế gần nhất
            $driver = $this->driverService->findNearestDriver([
                'pickup_lat' => $bookingData['pickup_lat'],
                'pickup_lng' => $bookingData['pickup_lng'],
                'vehicle_type' => $bookingData['vehicle_type'] ?? 'car'
            ]);

            if (!$driver) {
                throw new \Exception('Không tìm thấy tài xế phù hợp');
            }

            // 4. Tạo chuyến đi
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

            // 5. Gửi thông báo
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
                'message' => 'Đặt chuyến đi thành công!'
            ];

        } catch (\Exception $e) {
            // Rollback nếu cần
            $this->rollbackBooking($bookingData);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Hủy chuyến đi
     */
    public function cancelTrip(int $tripId, int $userId): array
    {
        try {
            // 1. Xác thực user
            $user = $this->userService->authenticate($userId);
            if (!$user) {
                throw new \Exception('User không hợp lệ');
            }

            // 2. Lấy thông tin chuyến đi
            $trip = $this->tripService->getTrip($tripId);
            if (!$trip || $trip['user_id'] != $userId) {
                throw new \Exception('Chuyến đi không tồn tại hoặc không thuộc về bạn');
            }

            // 3. Hủy chuyến đi
            $cancelledTrip = $this->tripService->cancelTrip($tripId);

            // 4. Hoàn tiền nếu cần
            if ($trip['status'] === 'confirmed') {
                $refund = $this->paymentService->processRefund($trip['payment_id']);
            }

            // 5. Gửi thông báo
            $this->notificationService->sendCancellationNotification([
                'user_id' => $userId,
                'driver_id' => $trip['driver_id'],
                'trip_id' => $tripId
            ]);

            return [
                'success' => true,
                'message' => 'Hủy chuyến đi thành công',
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
     * Theo dõi chuyến đi
     */
    public function trackTrip(int $tripId): array
    {
        $trip = $this->tripService->getTrip($tripId);
        if (!$trip) {
            return ['success' => false, 'message' => 'Chuyến đi không tồn tại'];
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
     * Đánh giá chuyến đi
     */
    public function rateTrip(int $tripId, int $userId, int $rating, string $comment = ''): array
    {
        try {
            // 1. Xác thực user
            $user = $this->userService->authenticate($userId);
            if (!$user) {
                throw new \Exception('User không hợp lệ');
            }

            // 2. Kiểm tra chuyến đi đã hoàn thành
            $trip = $this->tripService->getTrip($tripId);
            if (!$trip || $trip['status'] !== 'completed') {
                throw new \Exception('Chuyến đi chưa hoàn thành');
            }

            // 3. Lưu đánh giá
            $ratingData = $this->tripService->saveRating([
                'trip_id' => $tripId,
                'user_id' => $userId,
                'driver_id' => $trip['driver_id'],
                'rating' => $rating,
                'comment' => $comment
            ]);

            // 4. Gửi thông báo cho tài xế
            $this->notificationService->sendRatingNotification([
                'driver_id' => $trip['driver_id'],
                'rating' => $rating,
                'comment' => $comment
            ]);

            return [
                'success' => true,
                'message' => 'Đánh giá thành công',
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
     * Rollback khi có lỗi
     */
    private function rollbackBooking(array $bookingData): void
    {
        // Logic rollback nếu cần
        // Ví dụ: hoàn tiền, hủy chuyến đi, etc.
    }
}