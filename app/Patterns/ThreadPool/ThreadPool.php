<?php

namespace App\Patterns\ThreadPool;

/**
 * Thread Pool Pattern - Quản lý pool các worker threads
 *
 * Trong PHP, chúng ta sẽ mô phỏng Thread Pool bằng cách sử dụng:
 * - Queue để quản lý tasks
 * - Workers để xử lý tasks
 * - Process/Job để mô phỏng threading
 */
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
     * Khởi động thread pool
     */
    public function start(): void
    {
        $this->isRunning = true;
        echo "🚀 Thread Pool đã khởi động với {$this->maxWorkers} workers\n";

        // Khởi tạo workers
        for ($i = 0; $i < $this->maxWorkers; $i++) {
            $this->workers[] = new Worker($i + 1);
        }
    }

    /**
     * Dừng thread pool
     */
    public function stop(): void
    {
        $this->isRunning = false;
        echo "🛑 Thread Pool đã dừng\n";
    }

    /**
     * Thêm task vào queue
     */
    public function submitTask(Task $task): void
    {
        $this->taskQueue[] = $task;
        echo "📝 Task '{$task->getName()}' đã được thêm vào queue\n";
    }

    /**
     * Xử lý tất cả tasks trong queue
     */
    public function processTasks(): void
    {
        if (empty($this->taskQueue)) {
            echo "📭 Queue trống, không có task nào để xử lý\n";
            return;
        }

        echo "⚡ Bắt đầu xử lý " . count($this->taskQueue) . " tasks\n\n";

        $workerIndex = 0;
        foreach ($this->taskQueue as $task) {
            $worker = $this->workers[$workerIndex % $this->maxWorkers];

            try {
                echo "🔄 Worker {$worker->getId()} đang xử lý task: {$task->getName()}\n";

                $result = $worker->execute($task);

                if ($result['success']) {
                    $this->completedTasks[] = $result;
                    echo "✅ Task '{$task->getName()}' hoàn thành thành công\n";
                } else {
                    $this->failedTasks[] = $result;
                    echo "❌ Task '{$task->getName()}' thất bại: {$result['error']}\n";
                }

            } catch (\Exception $e) {
                $failedResult = [
                    'task' => $task->getName(),
                    'worker_id' => $worker->getId(),
                    'success' => false,
                    'error' => $e->getMessage()
                ];
                $this->failedTasks[] = $failedResult;
                echo "💥 Exception trong task '{$task->getName()}': {$e->getMessage()}\n";
            }

            $workerIndex++;
        }

        // Clear queue sau khi xử lý xong
        $this->taskQueue = [];

        echo "\n📊 Thống kê:\n";
        echo "   - Tasks hoàn thành: " . count($this->completedTasks) . "\n";
        echo "   - Tasks thất bại: " . count($this->failedTasks) . "\n";
    }

    /**
     * Lấy thống kê
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
     * Lấy danh sách tasks đã hoàn thành
     */
    public function getCompletedTasks(): array
    {
        return $this->completedTasks;
    }

    /**
     * Lấy danh sách tasks thất bại
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
        echo "🔄 Thread Pool đã được reset\n";
    }
}

/**
 * Worker class - Đại diện cho một worker thread
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
     * Thực thi task
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