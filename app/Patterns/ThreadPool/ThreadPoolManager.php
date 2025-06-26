<?php

namespace App\Patterns\ThreadPool;

use App\Patterns\ThreadPool\Tasks\EmailNotificationTask;
use App\Patterns\ThreadPool\Tasks\PaymentProcessingTask;
use App\Patterns\ThreadPool\Tasks\LocationUpdateTask;
use App\Patterns\ThreadPool\Tasks\FareCalculationTask;
use App\Patterns\ThreadPool\Tasks\PushNotificationTask;
use App\Patterns\ThreadPool\Tasks\DataBackupTask;

/**
 * Thread Pool Manager - Demo class để sử dụng Thread Pool Pattern
 */
class ThreadPoolManager
{
    private ThreadPool $threadPool;

    public function __construct(int $maxWorkers = 3)
    {
        $this->threadPool = new ThreadPool($maxWorkers);
    }

    /**
     * Demo cơ bản về Thread Pool
     */
    public function demonstrateBasicUsage(): void
    {
        echo "=== DEMO THREAD POOL PATTERN - CƠ BẢN ===\n\n";

        // Khởi động thread pool
        $this->threadPool->start();

        // Thêm các tasks vào queue
        $this->threadPool->submitTask(new EmailNotificationTask('Gửi email xác nhận', [
            'recipient' => 'user1@email.com',
            'subject' => 'Xác nhận đặt chuyến đi',
            'message' => 'Chuyến đi của bạn đã được xác nhận'
        ]));

        $this->threadPool->submitTask(new PaymentProcessingTask('Xử lý thanh toán', [
            'amount' => 150000,
            'payment_method' => 'credit_card'
        ]));

        $this->threadPool->submitTask(new LocationUpdateTask('Cập nhật vị trí tài xế', [
            'driver_id' => 123,
            'lat' => 10.762622,
            'lng' => 106.660172
        ]));

        // Xử lý tất cả tasks
        $this->threadPool->processTasks();

        // Dừng thread pool
        $this->threadPool->stop();
    }

    /**
     * Demo xử lý nhiều tasks cùng lúc
     */
    public function demonstrateConcurrentProcessing(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - XỬ LÝ ĐỒNG THỜI ===\n\n";

        $this->threadPool->start();

        // Thêm nhiều tasks cùng lúc
        for ($i = 1; $i <= 8; $i++) {
            $this->threadPool->submitTask(new FareCalculationTask("Tính cước phí chuyến {$i}", [
                'distance' => rand(5, 20),
                'duration' => rand(10, 45),
                'vehicle_type' => ['car', 'bike', 'premium'][rand(0, 2)]
            ]));
        }

        $this->threadPool->processTasks();
        $this->threadPool->stop();
    }

    /**
     * Demo xử lý tasks với lỗi
     */
    public function demonstrateErrorHandling(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - XỬ LÝ LỖI ===\n\n";

        $this->threadPool->start();

        // Tasks thành công
        $this->threadPool->submitTask(new PushNotificationTask('Thông báo chuyến đi', [
            'user_id' => 1,
            'title' => 'Tài xế đang đến',
            'body' => 'Tài xế sẽ đến trong 5 phút'
        ]));

        // Task có thể thất bại (payment processing)
        $this->threadPool->submitTask(new PaymentProcessingTask('Thanh toán có thể thất bại', [
            'amount' => 200000,
            'payment_method' => 'expired_card'
        ]));

        // Task thành công khác
        $this->threadPool->submitTask(new LocationUpdateTask('Cập nhật vị trí', [
            'driver_id' => 456,
            'lat' => 10.800000,
            'lng' => 106.700000
        ]));

        $this->threadPool->processTasks();
        $this->threadPool->stop();
    }

    /**
     * Demo workload thực tế
     */
    public function demonstrateRealWorldWorkload(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - WORKLOAD THỰC TẾ ===\n\n";

        $this->threadPool->start();

        // Simulate real-world workload
        $tasks = [
            // Email notifications
            new EmailNotificationTask('Email chào mừng', [
                'recipient' => 'newuser@email.com',
                'subject' => 'Chào mừng đến với RideShare',
                'message' => 'Cảm ơn bạn đã đăng ký!'
            ]),
            new EmailNotificationTask('Email xác nhận thanh toán', [
                'recipient' => 'user2@email.com',
                'subject' => 'Xác nhận thanh toán',
                'message' => 'Thanh toán của bạn đã được xử lý'
            ]),

            // Payment processing
            new PaymentProcessingTask('Thanh toán chuyến đi 1', [
                'amount' => 85000,
                'payment_method' => 'momo'
            ]),
            new PaymentProcessingTask('Thanh toán chuyến đi 2', [
                'amount' => 120000,
                'payment_method' => 'zalo_pay'
            ]),

            // Location updates
            new LocationUpdateTask('Cập nhật vị trí tài xế 1', [
                'driver_id' => 101,
                'lat' => 10.750000,
                'lng' => 106.650000
            ]),
            new LocationUpdateTask('Cập nhật vị trí tài xế 2', [
                'driver_id' => 102,
                'lat' => 10.780000,
                'lng' => 106.680000
            ]),

            // Fare calculations
            new FareCalculationTask('Tính cước xe máy', [
                'distance' => 8.5,
                'duration' => 25,
                'vehicle_type' => 'bike'
            ]),
            new FareCalculationTask('Tính cước xe hơi', [
                'distance' => 15.2,
                'duration' => 35,
                'vehicle_type' => 'car'
            ]),

            // Push notifications
            new PushNotificationTask('Thông báo tài xế đến', [
                'user_id' => 201,
                'title' => 'Tài xế đã đến',
                'body' => 'Vui lòng ra ngoài để lên xe'
            ]),
            new PushNotificationTask('Thông báo hoàn thành chuyến đi', [
                'user_id' => 202,
                'title' => 'Chuyến đi hoàn thành',
                'body' => 'Cảm ơn bạn đã sử dụng dịch vụ'
            ]),

            // Data backup
            new DataBackupTask('Backup bảng users', [
                'table' => 'users',
                'backup_type' => 'daily'
            ]),
            new DataBackupTask('Backup bảng trips', [
                'table' => 'trips',
                'backup_type' => 'daily'
            ])
        ];

        // Submit tất cả tasks
        foreach ($tasks as $task) {
            $this->threadPool->submitTask($task);
        }

        // Xử lý tất cả tasks
        $this->threadPool->processTasks();

        // Hiển thị thống kê chi tiết
        $this->displayDetailedStats();

        $this->threadPool->stop();
    }

    /**
     * Hiển thị thống kê chi tiết
     */
    private function displayDetailedStats(): void
    {
        $stats = $this->threadPool->getStats();
        $completedTasks = $this->threadPool->getCompletedTasks();
        $failedTasks = $this->threadPool->getFailedTasks();

        echo "\n📊 THỐNG KÊ CHI TIẾT:\n";
        echo "   - Số workers tối đa: {$stats['max_workers']}\n";
        echo "   - Workers đang hoạt động: {$stats['active_workers']}\n";
        echo "   - Tasks trong queue: {$stats['queue_size']}\n";
        echo "   - Tasks hoàn thành: {$stats['completed_tasks']}\n";
        echo "   - Tasks thất bại: {$stats['failed_tasks']}\n";
        echo "   - Trạng thái pool: " . ($stats['is_running'] ? 'Đang chạy' : 'Đã dừng') . "\n";

        if (!empty($completedTasks)) {
            echo "\n✅ TASKS HOÀN THÀNH:\n";
            foreach ($completedTasks as $task) {
                echo "   - {$task['task']} (Worker {$task['worker_id']}) - {$task['execution_time']}s\n";
            }
        }

        if (!empty($failedTasks)) {
            echo "\n❌ TASKS THẤT BẠI:\n";
            foreach ($failedTasks as $task) {
                echo "   - {$task['task']} (Worker {$task['worker_id']}): {$task['error']}\n";
            }
        }
    }

    /**
     * Demo so sánh hiệu suất
     */
    public function demonstratePerformanceComparison(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - SO SÁNH HIỆU SUẤT ===\n\n";

        // Test với 1 worker (sequential)
        echo "🔄 Test với 1 worker (sequential):\n";
        $sequentialPool = new ThreadPool(1);
        $startTime = microtime(true);

        $sequentialPool->start();
        for ($i = 1; $i <= 5; $i++) {
            $sequentialPool->submitTask(new FareCalculationTask("Tính cước {$i}", [
                'distance' => rand(5, 15),
                'duration' => rand(10, 30),
                'vehicle_type' => 'car'
            ]));
        }
        $sequentialPool->processTasks();
        $sequentialPool->stop();

        $sequentialTime = microtime(true) - $startTime;

        // Test với 5 workers (parallel)
        echo "\n🔄 Test với 5 workers (parallel):\n";
        $parallelPool = new ThreadPool(5);
        $startTime = microtime(true);

        $parallelPool->start();
        for ($i = 1; $i <= 5; $i++) {
            $parallelPool->submitTask(new FareCalculationTask("Tính cước {$i}", [
                'distance' => rand(5, 15),
                'duration' => rand(10, 30),
                'vehicle_type' => 'car'
            ]));
        }
        $parallelPool->processTasks();
        $parallelPool->stop();

        $parallelTime = microtime(true) - $startTime;

        echo "\n📈 KẾT QUẢ SO SÁNH:\n";
        echo "   - Sequential (1 worker): " . round($sequentialTime, 3) . "s\n";
        echo "   - Parallel (5 workers): " . round($parallelTime, 3) . "s\n";
        echo "   - Cải thiện: " . round((($sequentialTime - $parallelTime) / $sequentialTime) * 100, 1) . "%\n";
    }
}