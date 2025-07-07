<?php

namespace App\Patterns\ThreadPool;

class ThreadPoolManager
{
    private ThreadPool $threadPool;

    public function __construct(int $maxWorkers = 5)
    {
        $this->threadPool = new ThreadPool($maxWorkers);
    }

    /**
     * Demonstrate concurrent processing with real-world workload
     */
    public function demonstrateConcurrentProcessing(): void
    {
        $this->displayDemoHeader();

        $this->threadPool->start();
        $this->submitRealWorldTasks();
        $this->processTasksConcurrently();
        $this->threadPool->stop();

        $this->displayDetailedStatistics();
    }

    /**
     * Get thread pool statistics
     */
    public function getStats(): array
    {
        return $this->threadPool->getStats();
    }

    /**
     * Get thread pool instance
     */
    public function getThreadPool(): ThreadPool
    {
        return $this->threadPool;
    }

    /**
     * Reset thread pool
     */
    public function reset(): void
    {
        $this->threadPool->reset();
    }

    /**
     * Display demo header
     */
    private function displayDemoHeader(): void
    {
        echo "=== DEMO THREAD POOL - CONCURRENT BACKGROUND PROCESSING ===\n\n";
    }

    /**
     * Submit real-world workload tasks
     */
    private function submitRealWorldTasks(): void
    {
        echo "📋 Submitting real-world workload tasks...\n\n";

        $this->submitEmailNotificationTasks();
        $this->submitPaymentProcessingTasks();
        $this->submitLocationUpdateTasks();
        $this->submitFareCalculationTasks();
        $this->submitPushNotificationTasks();
    }

    /**
     * Submit email notification tasks
     */
    private function submitEmailNotificationTasks(): void
    {
        $emailTasks = [
            [
                'recipient' => 'newuser@email.com',
                'subject' => 'Welcome to RideShare',
                'message' => 'Thank you for registering!'
            ],
            [
                'recipient' => 'user2@email.com',
                'subject' => 'Payment confirmation',
                'message' => 'Your payment has been processed'
            ],
            [
                'recipient' => 'driver@email.com',
                'subject' => 'New trip request',
                'message' => 'You have a new trip request'
            ]
        ];

        foreach ($emailTasks as $taskData) {
            $this->threadPool->submitTask('email_notification', $taskData);
        }
    }

    /**
     * Submit payment processing tasks
     */
    private function submitPaymentProcessingTasks(): void
    {
        $paymentTasks = [
            [
                'amount' => 85000,
                'payment_method' => 'momo'
            ],
            [
                'amount' => 120000,
                'payment_method' => 'zalo_pay'
            ],
            [
                'amount' => 95000,
                'payment_method' => 'credit_card'
            ]
        ];

        foreach ($paymentTasks as $taskData) {
            $this->threadPool->submitTask('payment_processing', $taskData);
        }
    }

    /**
     * Submit location update tasks
     */
    private function submitLocationUpdateTasks(): void
    {
        $locationTasks = [
            [
                'driver_id' => 101,
                'lat' => 10.750000,
                'lng' => 106.650000
            ],
            [
                'driver_id' => 102,
                'lat' => 10.780000,
                'lng' => 106.680000
            ],
            [
                'driver_id' => 103,
                'lat' => 10.770000,
                'lng' => 106.670000
            ]
        ];

        foreach ($locationTasks as $taskData) {
            $this->threadPool->submitTask('location_update', $taskData);
        }
    }

    /**
     * Submit fare calculation tasks
     */
    private function submitFareCalculationTasks(): void
    {
        $fareCalculationTasks = [
            [
                'distance' => 8.5,
                'duration' => 25,
                'vehicle_type' => 'bike'
            ],
            [
                'distance' => 15.2,
                'duration' => 35,
                'vehicle_type' => 'car'
            ],
            [
                'distance' => 12.8,
                'duration' => 30,
                'vehicle_type' => 'premium'
            ]
        ];

        foreach ($fareCalculationTasks as $taskData) {
            $this->threadPool->submitTask('fare_calculation', $taskData);
        }
    }

    /**
     * Submit push notification tasks
     */
    private function submitPushNotificationTasks(): void
    {
        $pushNotificationTasks = [
            [
                'user_id' => 201,
                'title' => 'Driver arrived',
                'body' => 'Vui lòng ra ngoài để lên xe'
            ],
            [
                'user_id' => 202,
                'title' => 'Trip completed',
                'body' => 'Thank you for using our service'
            ],
            [
                'user_id' => 203,
                'title' => 'Payment successful',
                'body' => 'Your payment has been processed successfully'
            ]
        ];

        foreach ($pushNotificationTasks as $taskData) {
            $this->threadPool->submitTask('push_notification', $taskData);
        }
    }



    /**
     * Process tasks concurrently in background
     */
    private function processTasksConcurrently(): void
    {
        echo "\n🚀 Processing all tasks concurrently in background...\n";
        $this->threadPool->processTasks();
    }

    /**
     * Display detailed statistics
     */
    private function displayDetailedStatistics(): void
    {
        $stats = $this->threadPool->getStats();

        $this->displaySeparator();
        $this->displayStatisticsHeader();
        $this->displaySeparator();

        $this->displayPoolInformation($stats);
        $this->displayTaskResults($stats);

        $this->displaySeparator();
    }

    /**
     * Display separator line
     */
    private function displaySeparator(): void
    {
        echo str_repeat("=", 50) . "\n";
    }

    /**
     * Display statistics header
     */
    private function displayStatisticsHeader(): void
    {
        echo "📊 DETAILED STATISTICS\n";
    }

    /**
     * Display pool information
     */
    private function displayPoolInformation(array $stats): void
    {
        echo "Pool Information:\n";
        echo "  - Pool ID: {$stats['pool_id']}\n";
        echo "  - Max Workers: {$stats['max_workers']}\n";
        echo "  - Processing Mode: Laravel Queue Background Jobs\n";
        echo "\n";
    }

    /**
     * Display task results
     */
    private function displayTaskResults(array $stats): void
    {
        $successRate = $this->calculateSuccessRate($stats);

        echo "Task Results:\n";
        echo "  - Total Tasks: {$stats['total_tasks']}\n";
        echo "  - Completed: {$stats['completed_tasks']}\n";
        echo "  - Failed: {$stats['failed_tasks']}\n";
        echo "  - Success Rate: {$successRate}%\n";
        echo "\n";
    }

    /**
     * Calculate success rate
     */
    private function calculateSuccessRate(array $stats): float
    {
        if ($stats['total_tasks'] == 0) {
            return 0;
        }

        return round(($stats['completed_tasks'] / $stats['total_tasks']) * 100, 1);
    }
}
