<?php

namespace App\Patterns\ThreadPool;

class ThreadPool
{
    private array $workers = [];
    private array $taskQueue = [];
    private int $maxWorkers;
    private bool $isRunning = false;
    private array $completedTasks = [];
    private array $failedTasks = [];

    public function __construct(int $maxWorkers = 5)
    {
        $this->maxWorkers = $maxWorkers;
    }

    /**
     * Start thread pool
     */
    public function start(): void
    {
        $this->isRunning = true;
        echo "🚀 Thread Pool started with {$this->maxWorkers} workers\n";

        // Initialize workers
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            $this->workers[] = new Worker($i + 1);
        }
    }

    /**
     * Stop thread pool
     */
    public function stop(): void
    {
        $this->isRunning = false;
        echo "🛑 Thread Pool stopped\n";
    }

    /**
     * Add task to queue
     */
    public function submitTask(Task $task): void
    {
        $this->taskQueue[] = $task;
        echo "📝 Task '{$task->getName()}' added to queue\n";
    }

    /**
     * Process all tasks in queue
     */
    public function processTasks(): void
    {
        if (empty($this->taskQueue)) {
            echo "📭 Queue is empty, no tasks to process\n";
            return;
        }

        echo "⚡ Starting to process " . count($this->taskQueue) . " tasks\n\n";

        $workerIndex = 0;
        foreach ($this->taskQueue as $task) {
            $worker = $this->workers[$workerIndex % $this->maxWorkers];

            try {
                echo "🔄 Worker {$worker->getId()} is processing task: {$task->getName()}\n";

                $result = $worker->execute($task);

                if ($result['success']) {
                    $this->completedTasks[] = $result;
                    echo "✅ Task '{$task->getName()}' completed successfully\n";
                } else {
                    $this->failedTasks[] = $result;
                    echo "❌ Task '{$task->getName()}' failed: {$result['error']}\n";
                }

            } catch (\Exception $e) {
                $failedResult = [
                    'task' => $task->getName(),
                    'worker_id' => $worker->getId(),
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $this->failedTasks[] = $failedResult;
                echo "💥 Exception in task '{$task->getName()}': {$e->getMessage()}\n";
            }

            $workerIndex++;
        }

        // Clear queue after processing
        $this->taskQueue = [];

        echo "\n📊 Statistics:\n";
        echo "   - Completed tasks: " . count($this->completedTasks) . "\n";
        echo "   - Failed tasks: " . count($this->failedTasks) . "\n";
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        return [
            'max_workers' => $this->maxWorkers,
            'active_workers' => count($this->workers),
            'queue_size' => count($this->taskQueue),
            'completed_tasks' => count($this->completedTasks),
            'failed_tasks' => count($this->failedTasks),
            'is_running' => $this->isRunning
        ];
    }

    /**
     * Get completed tasks
     */
    public function getCompletedTasks(): array
    {
        return $this->completedTasks;
    }

    /**
     * Get failed tasks
     */
    public function getFailedTasks(): array
    {
        return $this->failedTasks;
    }

    /**
     * Reset pool
     */
    public function reset(): void
    {
        $this->taskQueue = [];
        $this->completedTasks = [];
        $this->failedTasks = [];
        echo "🔄 Thread Pool reset\n";
    }
}

/**
 * Worker class - Represents a worker thread
 */
class Worker
{
    private int $id;
    private bool $isBusy = false;

    public function __construct(int $id)
    {
        $this->id = $id;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isBusy(): bool
    {
        return $this->isBusy;
    }

    /**
     * Execute task
     * @param Task $task
     *
     * @return array
     */
    public function execute(Task $task): array
    {
        $this->isBusy = true;

        try {
            $startTime = microtime(true);
            $result = $task->execute();
            $executionTime = microtime(true) - $startTime;

            $this->isBusy = false;

            return [
                'task' => $task->getName(),
                'worker_id' => $this->id,
                'success' => true,
                'result' => $result,
                'execution_time' => round($executionTime, 3)
            ];

        } catch (\Exception $e) {
            $this->isBusy = false;

            return [
                'task' => $task->getName(),
                'worker_id' => $this->id,
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
}

/**
 * Task interface
 */
interface TaskInterface
{
    public function getName(): string;
    public function execute(): mixed;
}

/**
 * Abstract Task class
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