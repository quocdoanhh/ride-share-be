<?php

namespace App\Patterns\Facade\Services;

class NotificationService
{
    public function sendBookingConfirmation(array $data): bool
    {
        // Simulate sending booking confirmation
        echo "📱 Send booking confirmation:\n";
        echo "   - User ID: {$data['user_id']}\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Trip ID: {$data['trip_id']}\n";
        echo "   - Pickup location: {$data['pickup_location']}\n";
        echo "   - Estimated arrival: {$data['estimated_arrival']}\n\n";

        return true;
    }

    public function sendCancellationNotification(array $data): bool
    {
        // Simulate sending cancellation notification
        echo "📱 Send cancellation notification:\n";
        echo "   - User ID: {$data['user_id']}\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Trip ID: {$data['trip_id']}\n\n";

        return true;
    }

    public function sendReminderNotification(array $data): bool
    {
        // Simulate sending reminder notification
        echo "📱 Send reminder notification:\n";
        echo "   - User ID: {$data['user_id']}\n";
        echo "   - Trip ID: {$data['trip_id']}\n\n";

        return true;
    }

    public function sendRatingNotification(array $data): bool
    {
        // Simulate sending rating notification
        echo "📱 Send rating notification:\n";
        echo "   - Driver ID: {$data['driver_id']}\n";
        echo "   - Rating: {$data['rating']}/5\n";
        echo "   - Comment: {$data['comment']}\n\n";

        return true;
    }
}