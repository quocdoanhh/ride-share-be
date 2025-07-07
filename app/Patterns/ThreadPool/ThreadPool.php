<?php

namespace App\Patterns\ThreadPool;

use App\Jobs\ThreadPoolJob;
use Illuminate\Support\Facades\Cache;

/**
 * ThreadPool - Background concurrent processing using Laravel Queue
 *
 * This class manages a pool of workers that process tasks concurrently
 * in the background using Laravel's queue system.
 */
class ThreadPool
{
    private const CACHE_TTL = 3600; // 1 hour
    private const MAX_WAIT_TIME = 60; // 60 seconds
    private const CHECK_INTERVAL = 1; // 1 second

    private string $poolId;
    private int $maxWorkers;
    private array $workers = [];
    private array $taskQueue = [];
    private bool $isRunning = false;

    public function __construct(int $maxWorkers = 5)
    {
        $this->maxWorkers = $maxWorkers;
        $this->poolId = $this->generatePoolId();
        $this->initializeWorkers();
    }

    /**
     * Start the thread pool
     */
    public function start(): void
    {
        $this->isRunning = true;
        $this->clearPreviousResults();

        $this->displayStartMessage();
    }

    /**
     * Stop the thread pool
     */
    public function stop(): void
    {
        $this->isRunning = false;
        echo "🛑 ThreadPool stopped\n";
    }

    /**
     * Submit a task to the queue
     */
    public function submitTask(string $taskName, array $taskData = []): void
    {
        $this->taskQueue[] = [
            'name' => $taskName,
            'data' => $taskData
        ];

        echo "📝 Task '{$taskName}' added to queue\n";
    }

    /**
     * Process all tasks in the queue using background workers
     */
    public function processTasks(): void
    {
        if ($this->isQueueEmpty()) {
            echo "📭 Queue is empty, no tasks to process\n";
            return;
        }

        $taskCount = count($this->taskQueue);
        $this->displayProcessingMessage($taskCount);

        $startTime = microtime(true);

        $this->dispatchTasksToBackground();
        $this->waitForCompletion($taskCount);

        $totalTime = microtime(true) - $startTime;
        $this->displayCompletionMessage($totalTime);
    }

    /**
     * Get thread pool statistics
     */
    public function getStats(): array
    {
        $results = $this->getResultsFromCache();
        $completedTasks = $this->filterCompletedTasks($results);
        $failedTasks = $this->filterFailedTasks($results);

        return [
            'pool_id' => $this->poolId,
            'max_workers' => $this->maxWorkers,
            'active_workers' => count($this->workers),
            'queue_size' => count($this->taskQueue),
            'completed_tasks' => count($completedTasks),
            'failed_tasks' => count($failedTasks),
            'total_tasks' => count($results),
            'is_running' => $this->isRunning
        ];
    }

    /**
     * Reset the thread pool
     */
    public function reset(): void
    {
        $this->taskQueue = [];
        $this->clearResults();
        echo "🔄 ThreadPool reset\n";
    }

    /**
     * Generate unique pool ID
     */
    private function generatePoolId(): string
    {
        return 'pool_' . uniqid();
    }

    /**
     * Initialize workers for queue distribution
     */
    private function initializeWorkers(): void
    {
        for ($i = 1; $i <= $this->maxWorkers; $i++) {
            $this->workers[] = new Worker($i);
        }
    }

    /**
     * Display start message
     */
    private function displayStartMessage(): void
    {
        echo "🚀 ThreadPool started with {$this->maxWorkers} workers (Background Processing)\n";
        echo "   - Pool ID: {$this->poolId}\n";
        echo "   - Processing mode: Laravel Queue Background Jobs\n";
    }

    /**
     * Display processing message
     */
    private function displayProcessingMessage(int $taskCount): void
    {
        echo "⚡ Dispatching {$taskCount} tasks to {$this->maxWorkers} workers in background\n\n";
    }

    /**
     * Display completion message and statistics
     */
    private function displayCompletionMessage(float $totalTime): void
    {
        echo "✅ All tasks completed in " . round($totalTime, 3) . "s\n";
        $this->displayStatistics($totalTime);
    }

    /**
     * Check if queue is empty
     */
    private function isQueueEmpty(): bool
    {
        return empty($this->taskQueue);
    }

    /**
     * Clear previous results from cache
     */
    private function clearPreviousResults(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Clear all results from cache
     */
    private function clearResults(): void
    {
        Cache::forget($this->getCacheKey());
    }

    /**
     * Dispatch tasks to background workers
     */
    private function dispatchTasksToBackground(): void
    {
        foreach ($this->taskQueue as $index => $task) {
            $workerId = $this->calculateWorkerId($index);
            $this->dispatchSingleTask($task, $workerId);
        }

        $this->displayDispatchCompleteMessage();
        $this->clearTaskQueue();
    }

    /**
     * Calculate worker ID using round-robin distribution
     */
    private function calculateWorkerId(int $index): int
    {
        return ($index % $this->maxWorkers) + 1;
    }

    /**
     * Dispatch a single task to a worker
     */
    private function dispatchSingleTask(array $task, int $workerId): void
    {
        echo "📋 Dispatching task '{$task['name']}' to Worker {$workerId}\n";

        ThreadPoolJob::dispatch(
            $task['name'],
            $task['data'],
            $workerId,
            $this->poolId
        );
    }

    /**
     * Display dispatch complete message
     */
    private function displayDispatchCompleteMessage(): void
    {
        echo "\n🚀 All tasks dispatched to background workers!\n";
        echo "💫 Workers are now processing tasks concurrently in background...\n\n";
    }

    /**
     * Clear task queue after dispatching
     */
    private function clearTaskQueue(): void
    {
        $this->taskQueue = [];
    }

        /**
     * Wait for all tasks to complete
     */
    private function waitForCompletion(int $expectedTaskCount): void
    {
        echo "⏳ Waiting for background tasks to complete...\n";

        $waitTime = 0;
        while ($waitTime < self::MAX_WAIT_TIME) {
            $completedCount = $this->getCompletedTaskCount();

            if ($this->allTasksCompleted($completedCount, $expectedTaskCount)) {
                echo "🎉 All {$expectedTaskCount} tasks completed!\n";
                break;
            }

            $this->waitForNextCheck();
            $waitTime += self::CHECK_INTERVAL;
        }

        if ($waitTime >= self::MAX_WAIT_TIME) {
            echo "⚠️  Timeout reached, some tasks may still be processing in background\n";
        }
    }

    /**
     * Get completed task count
     */
    private function getCompletedTaskCount(): int
    {
        return count($this->getResultsFromCache());
    }

    /**
     * Check if all tasks are completed
     */
    private function allTasksCompleted(int $completedCount, int $expectedCount): bool
    {
        return $completedCount >= $expectedCount;
    }



    /**
     * Wait for next check
     */
    private function waitForNextCheck(): void
    {
        sleep(self::CHECK_INTERVAL);
    }

    /**
     * Get results from cache
     */
    private function getResultsFromCache(): array
    {
        return Cache::get($this->getCacheKey(), []);
    }

    /**
     * Get cache key for results
     */
    private function getCacheKey(): string
    {
        return "threadpool_results_{$this->poolId}";
    }

    /**
     * Filter completed tasks
     */
    private function filterCompletedTasks(array $results): array
    {
        return array_filter($results, fn($r) => $r['success'] === true);
    }

    /**
     * Filter failed tasks
     */
    private function filterFailedTasks(array $results): array
    {
        return array_filter($results, fn($r) => $r['success'] === false);
    }

    /**
     * Display detailed statistics
     */
    private function displayStatistics(float $totalTime): void
    {
        $results = $this->getResultsFromCache();
        $completedTasks = $this->filterCompletedTasks($results);
        $failedTasks = $this->filterFailedTasks($results);

        echo "\n📊 Statistics:\n";
        echo "   - Total tasks: " . count($results) . "\n";
        echo "   - Completed tasks: " . count($completedTasks) . "\n";
        echo "   - Failed tasks: " . count($failedTasks) . "\n";
        echo "   - Total processing time: " . round($totalTime, 3) . "s\n";
        echo "   - Workers used: {$this->maxWorkers}\n";

        $this->displayWorkerDistribution($results);
        $this->displayAverageExecutionTime($results);
    }

    /**
     * Display worker distribution
     */
    private function displayWorkerDistribution(array $results): void
    {
        $workerStats = $this->calculateWorkerStats($results);

        echo "\n👥 Worker Distribution:\n";
        foreach ($workerStats as $workerId => $count) {
            echo "   - Worker {$workerId}: {$count} tasks\n";
        }
    }

    /**
     * Calculate worker statistics
     */
    private function calculateWorkerStats(array $results): array
    {
        $workerStats = [];
        foreach ($results as $result) {
            $workerId = $result['worker_id'] ?? 'unknown';
            $workerStats[$workerId] = ($workerStats[$workerId] ?? 0) + 1;
        }
        return $workerStats;
    }

    /**
     * Display average execution time
     */
    private function displayAverageExecutionTime(array $results): void
    {
        $totalExecutionTime = array_sum(array_column($results, 'execution_time'));
        $avgExecutionTime = count($results) > 0 ? round($totalExecutionTime / count($results), 3) : 0;
        echo "   - Average execution time: {$avgExecutionTime}s per task\n";
    }
}

/**
 * Worker class for queue distribution
 */
class Worker
{
    private int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }
}

/**
 * Task interface - For compatibility
 */
interface TaskInterface
{
    public function getName(): string;
    public function execute(): mixed;
}

/**
 * Abstract Task class - For compatibility
 */
abstract class Task implements TaskInterface
{
    protected string $name;
    protected array $data;

    public function __construct(string $name, array $data = [])
    {
        $this->name = $name;
        $this->data = $data;
    }

    public function getName(): string
    {
        return $this->name;
    }

    abstract public function execute(): mixed;
}