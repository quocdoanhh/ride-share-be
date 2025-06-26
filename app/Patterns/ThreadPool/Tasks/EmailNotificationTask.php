<?php

namespace App\Patterns\ThreadPool\Tasks;

use App\Patterns\ThreadPool\Task;

/**
 * Task gửi email thông báo
 */
class EmailNotificationTask extends Task
{
    public function execute(): mixed
    {
        $recipient = $this->data['recipient'] ?? 'unknown@email.com';
        $subject = $this->data['subject'] ?? 'Thông báo từ RideShare';
        $message = $this->data['message'] ?? 'Nội dung thông báo';

        // Simulate email sending
        usleep(rand(100000, 500000)); // 0.1 - 0.5 seconds

        echo "   📧 Gửi email đến: {$recipient}\n";
        echo "   📧 Tiêu đề: {$subject}\n";

        return [
            'recipient' => $recipient,
            'subject' => $subject,
            'status' => 'sent',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}

/**
 * Task xử lý thanh toán
 */
class PaymentProcessingTask extends Task
{
    public function execute(): mixed
    {
        $amount = $this->data['amount'] ?? 0;
        $paymentMethod = $this->data['payment_method'] ?? 'credit_card';

        // Simulate payment processing
        usleep(rand(200000, 800000)); // 0.2 - 0.8 seconds

        $success = rand(1, 10) > 2; // 80% success rate

        echo "   💳 Xử lý thanh toán: {$amount} VND\n";
        echo "   💳 Phương thức: {$paymentMethod}\n";

        if ($success) {
            return [
                'amount' => $amount,
                'payment_method' => $paymentMethod,
                'status' => 'success',
                'transaction_id' => 'TXN_' . time() . '_' . rand(1000, 9999)
            ];
        } else {
            throw new \Exception('Thanh toán thất bại - thẻ bị từ chối');
        }
    }
}

/**
 * Task cập nhật vị trí tài xế
 */
class LocationUpdateTask extends Task
{
    public function execute(): mixed
    {
        $driverId = $this->data['driver_id'] ?? 0;
        $lat = $this->data['lat'] ?? 0;
        $lng = $this->data['lng'] ?? 0;

        // Simulate location update
        usleep(rand(50000, 200000)); // 0.05 - 0.2 seconds

        echo "   📍 Cập nhật vị trí tài xế {$driverId}\n";
        echo "   📍 Tọa độ: {$lat}, {$lng}\n";

        return [
            'driver_id' => $driverId,
            'lat' => $lat,
            'lng' => $lng,
            'timestamp' => now()->toDateTimeString(),
            'status' => 'updated'
        ];
    }
}

/**
 * Task tính toán cước phí
 */
class FareCalculationTask extends Task
{
    public function execute(): mixed
    {
        $distance = $this->data['distance'] ?? 0;
        $duration = $this->data['duration'] ?? 0;
        $vehicleType = $this->data['vehicle_type'] ?? 'car';

        // Simulate fare calculation
        usleep(rand(100000, 300000)); // 0.1 - 0.3 seconds

        $baseFare = 10000; // 10,000 VND base fare
        $distanceFare = $distance * 15000; // 15,000 VND per km
        $timeFare = $duration * 2000; // 2,000 VND per minute

        $totalFare = $baseFare + $distanceFare + $timeFare;

        // Apply vehicle type multiplier
        $multipliers = [
            'car' => 1.0,
            'bike' => 0.7,
            'premium' => 1.5
        ];

        $totalFare *= $multipliers[$vehicleType] ?? 1.0;

        echo "   🧮 Tính cước phí cho {$vehicleType}\n";
        echo "   🧮 Khoảng cách: {$distance}km, Thời gian: {$duration} phút\n";
        echo "   🧮 Tổng cước: " . number_format($totalFare) . " VND\n";

        return [
            'distance' => $distance,
            'duration' => $duration,
            'vehicle_type' => $vehicleType,
            'base_fare' => $baseFare,
            'distance_fare' => $distanceFare,
            'time_fare' => $timeFare,
            'total_fare' => $totalFare,
            'multiplier' => $multipliers[$vehicleType] ?? 1.0
        ];
    }
}

/**
 * Task gửi push notification
 */
class PushNotificationTask extends Task
{
    public function execute(): mixed
    {
        $userId = $this->data['user_id'] ?? 0;
        $title = $this->data['title'] ?? 'Thông báo';
        $body = $this->data['body'] ?? 'Nội dung thông báo';

        // Simulate push notification
        usleep(rand(80000, 250000)); // 0.08 - 0.25 seconds

        echo "   🔔 Gửi push notification cho user {$userId}\n";
        echo "   🔔 Tiêu đề: {$title}\n";

        return [
            'user_id' => $userId,
            'title' => $title,
            'body' => $body,
            'status' => 'sent',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}

/**
 * Task backup dữ liệu
 */
class DataBackupTask extends Task
{
    public function execute(): mixed
    {
        $table = $this->data['table'] ?? 'users';
        $backupType = $this->data['backup_type'] ?? 'daily';

        // Simulate data backup
        usleep(rand(500000, 1500000)); // 0.5 - 1.5 seconds

        echo "   💾 Backup dữ liệu bảng: {$table}\n";
        echo "   💾 Loại backup: {$backupType}\n";

        return [
            'table' => $table,
            'backup_type' => $backupType,
            'backup_file' => "backup_{$table}_{$backupType}_" . date('Y-m-d_H-i-s') . ".sql",
            'size' => rand(1024, 10240) . ' KB',
            'status' => 'completed',
            'timestamp' => now()->toDateTimeString()
        ];
    }
}