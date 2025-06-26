<?php

namespace App\Patterns\ThreadPool;

use App\Patterns\ThreadPool\Tasks\EmailNotificationTask;
use App\Patterns\ThreadPool\Tasks\PaymentProcessingTask;
use App\Patterns\ThreadPool\Tasks\LocationUpdateTask;
use App\Patterns\ThreadPool\Tasks\FareCalculationTask;
use App\Patterns\ThreadPool\Tasks\PushNotificationTask;
use App\Patterns\ThreadPool\Tasks\DataBackupTask;

class ThreadPoolManager
{
    private ThreadPool $threadPool;

    public function __construct(int $maxWorkers = 3)
    {
        $this->threadPool = new ThreadPool($maxWorkers);
    }

    /**
     * Demo basic usage of Thread Pool
     */
    public function demonstrateBasicUsage(): void
    {
        echo "=== DEMO THREAD POOL PATTERN - BASIC ===\n\n";

        // Start thread pool
        $this->threadPool->start();

        // Add tasks to queue
        $this->threadPool->submitTask(new EmailNotificationTask('Send email confirmation', [
            'recipient' => 'user1@email.com',
            'subject' => 'Confirm trip booking',
            'message' => 'Your trip has been confirmed'
        ]));

        $this->threadPool->submitTask(new PaymentProcessingTask('Process payment', [
            'amount' => 150000,
            'payment_method' => 'credit_card'
        ]));

        $this->threadPool->submitTask(new LocationUpdateTask('Update driver location', [
            'driver_id' => 123,
            'lat' => 10.762622,
            'lng' => 106.660172
        ]));

        // Process all tasks
        $this->threadPool->processTasks();

        // Stop thread pool
        $this->threadPool->stop();
    }

    /**
     * Demo concurrent processing
     */
    public function demonstrateConcurrentProcessing(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - CONCURRENT PROCESSING ===\n\n";

        $this->threadPool->start();

        // Add multiple tasks
        for ($i = 1; $i <= 8; $i++) {
            $this->threadPool->submitTask(new FareCalculationTask("Calculate fare for trip {$i}", [
                'distance' => rand(5, 20),
                'duration' => rand(10, 45),
                'vehicle_type' => ['car', 'bike', 'premium'][rand(0, 2)]
            ]));
        }

        $this->threadPool->processTasks();
        $this->threadPool->stop();
    }

    /**
     * Demo error handling
     */
    public function demonstrateErrorHandling(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - ERROR HANDLING ===\n\n";

        $this->threadPool->start();

        // Successful tasks
        $this->threadPool->submitTask(new PushNotificationTask('Trip notification', [
            'user_id' => 1,
            'title' => 'Driver is on the way',
            'body' => 'Driver will arrive in 5 minutes'
        ]));

        // Task that can fail (payment processing)
        $this->threadPool->submitTask(new PaymentProcessingTask('Thanh toán có thể thất bại', [
            'amount' => 200000,
            'payment_method' => 'expired_card'
        ]));

        // Successful task
        $this->threadPool->submitTask(new LocationUpdateTask('Update driver location', [
            'driver_id' => 456,
            'lat' => 10.800000,
            'lng' => 106.700000
        ]));

        $this->threadPool->processTasks();
        $this->threadPool->stop();
    }

    /**
     * Demo real-world workload
     */
    public function demonstrateRealWorldWorkload(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - REAL-WORLD WORKLOAD ===\n\n";

        $this->threadPool->start();

        // Simulate real-world workload
        $tasks = [
            // Email notifications
            new EmailNotificationTask('Welcome email', [
                'recipient' => 'newuser@email.com',
                'subject' => 'Welcome to RideShare',
                'message' => 'Thank you for registering!'
            ]),
            new EmailNotificationTask('Payment confirmation email', [
                'recipient' => 'user2@email.com',
                'subject' => 'Payment confirmation',
                'message' => 'Your payment has been processed'
            ]),

            // Payment processing
            new PaymentProcessingTask('Payment for trip 1', [
                'amount' => 85000,
                'payment_method' => 'momo'
            ]),
            new PaymentProcessingTask('Payment for trip 2', [
                'amount' => 120000,
                'payment_method' => 'zalo_pay'
            ]),

            // Location updates
            new LocationUpdateTask('Update driver location 1', [
                'driver_id' => 101,
                'lat' => 10.750000,
                'lng' => 106.650000
            ]),
            new LocationUpdateTask('Update driver location 2', [
                'driver_id' => 102,
                'lat' => 10.780000,
                'lng' => 106.680000
            ]),

            // Fare calculations
            new FareCalculationTask('Calculate bike fare', [
                'distance' => 8.5,
                'duration' => 25,
                'vehicle_type' => 'bike'
            ]),
            new FareCalculationTask('Calculate car fare', [
                'distance' => 15.2,
                'duration' => 35,
                'vehicle_type' => 'car'
            ]),

            // Push notifications
            new PushNotificationTask('Driver arrived notification', [
                'user_id' => 201,
                'title' => 'Driver arrived',
                'body' => 'Vui lòng ra ngoài để lên xe'
            ]),
            new PushNotificationTask('Trip completed notification', [
                'user_id' => 202,
                'title' => 'Trip completed',
                'body' => 'Thank you for using our service'
            ]),

            // Data backup
            new DataBackupTask('Backup users table', [
                'table' => 'users',
                'backup_type' => 'daily'
            ]),
            new DataBackupTask('Backup trips table', [
                'table' => 'trips',
                'backup_type' => 'daily'
            ])
        ];

        // Submit all tasks
        foreach ($tasks as $task) {
            $this->threadPool->submitTask($task);
        }

        // Process all tasks
        $this->threadPool->processTasks();

        // Display detailed statistics
        $this->displayDetailedStats();

        $this->threadPool->stop();
    }

    /**
     * Display detailed statistics
     */
    private function displayDetailedStats(): void
    {
        $stats = $this->threadPool->getStats();
        $completedTasks = $this->threadPool->getCompletedTasks();
        $failedTasks = $this->threadPool->getFailedTasks();

        echo "\n📊 DETAILED STATISTICS:\n";
        echo "   - Maximum workers: {$stats['max_workers']}\n";
        echo "   - Active workers: {$stats['active_workers']}\n";
        echo "   - Tasks in queue: {$stats['queue_size']}\n";
        echo "   - Completed tasks: {$stats['completed_tasks']}\n";
        echo "   - Failed tasks: {$stats['failed_tasks']}\n";
        echo "   - Pool status: " . ($stats['is_running'] ? 'Running' : 'Stopped') . "\n";

        if (!empty($completedTasks)) {
            echo "\n✅ COMPLETED TASKS:\n";
            foreach ($completedTasks as $task) {
                echo "   - {$task['task']} (Worker {$task['worker_id']}) - {$task['execution_time']}s\n";
            }
        }

        if (!empty($failedTasks)) {
            echo "\n❌ FAILED TASKS:\n";
            foreach ($failedTasks as $task) {
                echo "   - {$task['task']} (Worker {$task['worker_id']}): {$task['error']}\n";
            }
        }
    }

    /**
     * Demo performance comparison
     */
    public function demonstratePerformanceComparison(): void
    {
        echo "\n=== DEMO THREAD POOL PATTERN - PERFORMANCE COMPARISON ===\n\n";

        // Test with 1 worker (sequential)
        echo "🔄 Test with 1 worker (sequential):\n";
        $sequentialPool = new ThreadPool(1);
        $startTime = microtime(true);

        $sequentialPool->start();
        for ($i = 1; $i <= 5; $i++) {
            $sequentialPool->submitTask(new FareCalculationTask("Calculate fare for trip {$i}", [
                'distance' => rand(5, 15),
                'duration' => rand(10, 30),
                'vehicle_type' => 'car'
            ]));
        }
        $sequentialPool->processTasks();
        $sequentialPool->stop();

        $sequentialTime = microtime(true) - $startTime;

        // Test with 5 workers (parallel)
        echo "\n🔄 Test with 5 workers (parallel):\n";
        $parallelPool = new ThreadPool(5);
        $startTime = microtime(true);

        $parallelPool->start();
        for ($i = 1; $i <= 5; $i++) {
            $parallelPool->submitTask(new FareCalculationTask("Calculate fare for trip {$i}", [
                'distance' => rand(5, 15),
                'duration' => rand(10, 30),
                'vehicle_type' => 'car'
            ]));
        }
        $parallelPool->processTasks();
        $parallelPool->stop();

        $parallelTime = microtime(true) - $startTime;

        echo "\n📈 PERFORMANCE COMPARISON:\n";
        echo "   - Sequential (1 worker): " . round($sequentialTime, 3) . "s\n";
        echo "   - Parallel (5 workers): " . round($parallelTime, 3) . "s\n";
        echo "   - Improvement: " . round((($sequentialTime - $parallelTime) / $sequentialTime) * 100, 1) . "%\n";
    }
}