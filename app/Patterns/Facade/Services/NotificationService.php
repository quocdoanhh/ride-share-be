<?php

namespace App\Patterns\Facade\Services;

class NotificationService
{
    public function sendBookingConfirmation(array $data): bool
    {
        // Simulate sending booking confirmation
        echo "📱 Gửi thông báo xác nhận đặt chuyến đi:\n";
        echo "   - User ID: {$data['user_id']}\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Trip ID: {$data['trip_id']}\n";
        echo "   - Điểm đón: {$data['pickup_location']}\n";
        echo "   - Thời gian đến: {$data['estimated_arrival']}\n\n";

        return true;
    }

    public function sendCancellationNotification(array $data): bool
    {
        // Simulate sending cancellation notification
        echo "📱 Gửi thông báo hủy chuyến đi:\n";
        echo "   - User ID: {$data['user_id']}\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Trip ID: {$data['trip_id']}\n\n";

        return true;
    }

    public function sendRatingNotification(array $data): bool
    {
        // Simulate sending rating notification
        echo "📱 Gửi thông báo đánh giá:\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Rating: {$data['rating']}/5\n";
        echo "   - Comment: {$data['comment']}\n\n";

        return true;
    }
}