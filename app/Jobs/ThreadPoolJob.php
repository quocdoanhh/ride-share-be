<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ThreadPoolJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const CACHE_TTL = 3600; // 1 hour
    const MIN_PROCESSING_TIME = 1; // minimum seconds
    const MAX_PROCESSING_TIME = 3; // maximum seconds
    const SUCCESS_RATE = 100; // 100% success rate

    private string $taskName;
    private array $taskData;
    private int $workerId;
    private string $poolId;

    public function __construct(string $taskName, array $taskData, int $workerId, string $poolId)
    {
        $this->taskName = $taskName;
        $this->taskData = $taskData;
        $this->workerId = $workerId;
        $this->poolId = $poolId;

        $this->onQueue("worker_{$workerId}");
    }

    /**
     * Execute the job
     */
    public function handle(): void
    {
        $startTime = microtime(true);

        try {
            $this->displayStartMessage();
            $result = $this->processTask();
            $this->handleSuccess($result, $startTime);

        } catch (\Exception $e) {
            $this->handleFailure($e, $startTime);
        }
    }

    /**
     * Display start message
     */
    private function displayStartMessage(): void
    {
        echo "🔄 Worker {$this->workerId} starting task: {$this->taskName}\n";
    }

    /**
     * Handle successful task completion
     */
    private function handleSuccess(array $result, float $startTime): void
    {
        $executionTime = $this->calculateExecutionTime($startTime);

        echo "✅ Worker {$this->workerId} completed: {$this->taskName} ({$executionTime}s)\n";

        $this->storeResult([
            'task' => $this->taskName,
            'worker_id' => $this->workerId,
            'success' => true,
            'result' => $result,
            'execution_time' => $executionTime,
            'pool_id' => $this->poolId
        ]);
    }

    /**
     * Handle task failure
     */
    private function handleFailure(\Exception $e, float $startTime): void
    {
        $executionTime = $this->calculateExecutionTime($startTime);

        echo "❌ Worker {$this->workerId} failed: {$this->taskName} - {$e->getMessage()}\n";

        $this->storeResult([
            'task' => $this->taskName,
            'worker_id' => $this->workerId,
            'success' => false,
            'error' => $e->getMessage(),
            'execution_time' => $executionTime,
            'pool_id' => $this->poolId
        ]);

        $this->logFailure($e);
    }

    /**
     * Calculate execution time
     */
    private function calculateExecutionTime(float $startTime): float
    {
        return round(microtime(true) - $startTime, 3);
    }

    /**
     * Log task failure
     */
    private function logFailure(\Exception $e): void
    {
        Log::error("ThreadPool job failed", [
            'task' => $this->taskName,
            'worker_id' => $this->workerId,
            'error' => $e->getMessage()
        ]);
    }

    /**
     * Process task based on type
     */
    private function processTask(): array
    {
        $this->simulateProcessingTime();

        return match ($this->taskName) {
            'email_notification' => $this->processEmailNotification(),
            'payment_processing' => $this->processPayment(),
            'location_update' => $this->processLocationUpdate(),
            'fare_calculation' => $this->processFareCalculation(),
            'push_notification' => $this->processPushNotification(),

            default => $this->processGenericTask()
        };
    }

    /**
     * Simulate processing time
     */
    private function simulateProcessingTime(): void
    {
        $processingTime = rand(self::MIN_PROCESSING_TIME, self::MAX_PROCESSING_TIME);
        sleep($processingTime);
    }

    /**
     * Process email notification task
     */
    private function processEmailNotification(): array
    {
        return [
            'type' => 'email_notification',
            'recipient' => $this->getTaskDataValue('recipient', 'unknown'),
            'subject' => $this->getTaskDataValue('subject', 'No subject'),
            'status' => 'sent',
            'message_id' => $this->generateUniqueId()
        ];
    }

    /**
     * Process payment task with failure simulation
     */
    private function processPayment(): array
    {
        if (!$this->isTaskSuccessful()) {
            throw new \Exception('Payment failed: Insufficient funds');
        }

        return [
            'type' => 'payment_processing',
            'amount' => $this->getTaskDataValue('amount', 0),
            'payment_method' => $this->getTaskDataValue('payment_method', 'unknown'),
            'status' => 'completed',
            'transaction_id' => $this->generateTransactionId()
        ];
    }

    /**
     * Process location update task
     */
    private function processLocationUpdate(): array
    {
        return [
            'type' => 'location_update',
            'driver_id' => $this->getTaskDataValue('driver_id', 0),
            'latitude' => $this->getTaskDataValue('lat', 0),
            'longitude' => $this->getTaskDataValue('lng', 0),
            'timestamp' => now(),
            'status' => 'updated'
        ];
    }

    /**
     * Process fare calculation task
     */
    private function processFareCalculation(): array
    {
        $distance = $this->getTaskDataValue('distance', 0);
        $duration = $this->getTaskDataValue('duration', 0);
        $vehicleType = $this->getTaskDataValue('vehicle_type', 'car');

        $baseFare = $this->getBaseFareByVehicleType($vehicleType);
        $totalFare = $this->calculateTotalFare($baseFare, $distance, $duration);

        return [
            'type' => 'fare_calculation',
            'distance' => $distance,
            'duration' => $duration,
            'vehicle_type' => $vehicleType,
            'base_fare' => $baseFare,
            'total_fare' => $totalFare,
            'status' => 'calculated'
        ];
    }

    /**
     * Process push notification task
     */
    private function processPushNotification(): array
    {
        return [
            'type' => 'push_notification',
            'user_id' => $this->getTaskDataValue('user_id', 0),
            'title' => $this->getTaskDataValue('title', 'Notification'),
            'body' => $this->getTaskDataValue('body', 'No message'),
            'status' => 'sent',
            'notification_id' => $this->generateNotificationId()
        ];
    }



    /**
     * Process generic task
     */
    private function processGenericTask(): array
    {
        return [
            'type' => 'generic_task',
            'data' => $this->taskData,
            'status' => 'completed'
        ];
    }

    /**
     * Get task data value with default
     */
    private function getTaskDataValue(string $key, mixed $default): mixed
    {
        return $this->taskData[$key] ?? $default;
    }

    /**
     * Check if task should be successful based on success rate
     */
    private function isTaskSuccessful(): bool
    {
        return rand(1, 100) <= self::SUCCESS_RATE;
    }

    /**
     * Get base fare by vehicle type
     */
    private function getBaseFareByVehicleType(string $vehicleType): int
    {
        return match ($vehicleType) {
            'bike' => 15000,
            'car' => 25000,
            'premium' => 45000,
            default => 20000
        };
    }

    /**
     * Calculate total fare
     */
    private function calculateTotalFare(int $baseFare, float $distance, int $duration): int
    {
        return $baseFare + ($distance * 3000) + ($duration * 500);
    }

    /**
     * Generate unique ID
     */
    private function generateUniqueId(): string
    {
        return uniqid();
    }

    /**
     * Generate transaction ID
     */
    private function generateTransactionId(): string
    {
        return 'txn_' . uniqid();
    }

    /**
     * Generate notification ID
     */
    private function generateNotificationId(): string
    {
        return 'notif_' . uniqid();
    }



    /**
     * Store result in cache for statistics
     */
    private function storeResult(array $result): void
    {
        $cacheKey = $this->getCacheKey();
        $results = $this->getExistingResults($cacheKey);
        $results[] = $result;

        Cache::put($cacheKey, $results, self::CACHE_TTL);
    }

    /**
     * Get cache key for results
     */
    private function getCacheKey(): string
    {
        return "threadpool_results_{$this->poolId}";
    }

    /**
     * Get existing results from cache
     */
    private function getExistingResults(string $cacheKey): array
    {
        return Cache::get($cacheKey, []);
    }
}