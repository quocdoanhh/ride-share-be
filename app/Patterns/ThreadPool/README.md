# ThreadPool - Background Concurrent Processing

## Đặc điểm chính

### 🚀 Background Processing
- **Laravel Queue System** - Sử dụng queue cho background processing
- **Concurrent Workers** - Nhiều workers chạy song song
- **Round-robin Distribution** - Phân phối task đều cho các workers
- **Real-time Progress Tracking** - Theo dõi tiến trình xử lý

### 📊 Task Types
- **Email Notifications** - Gửi email
- **Payment Processing** - Xử lý thanh toán
- **Location Updates** - Cập nhật vị trí
- **Fare Calculations** - Tính cước phí
- **Push Notifications** - Gửi thông báo push

## Cấu trúc thư mục

```
app/
├── Jobs/
│   └── ThreadPoolJob.php          # Background job handler
├── Patterns/ThreadPool/
│   ├── ThreadPool.php             # Core ThreadPool class
│   ├── ThreadPoolManager.php      # Manager class
│   └── README.md
└── Console/Commands/
    └── DemoThreadPoolCommand.php  # Demo command
```

### 🔄 Flow Diagram
```
1. User Command
   php artisan demo:thread-pool --workers=5
       │
       ▼
2. DemoThreadPoolCommand.php
   handle() method
       │
       ▼
3. ThreadPoolManager.php
   demonstrateConcurrentProcessing()
       │
       ▼
4. ThreadPool.php
   start() → submitTask() → processTasks()
       │
       ▼
5. Laravel Queue System
   Dispatch ThreadPoolJob to worker queues
       │
       ▼
6. ThreadPoolJob.php (Background Processing)
   handle() → processTask() → storeResult()
       │
       ▼
7. Cache System
   Store results for final statistics
       │
       ▼
8. ThreadPool.php
   waitForCompletion() → displayStatistics()
```

### 🏗️ Kiến trúc xử lý song song

```
┌─────────────────────────────────────────┐
│         Command Entry Point             │
│    DemoThreadPoolCommand.php            │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         ThreadPoolManager               │
│   - submitEmailNotificationTasks()      │
│   - submitPaymentProcessingTasks()      │
│   - submitLocationUpdateTasks()         │
│   - submitFareCalculationTasks()        │
│   - submitPushNotificationTasks()       │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         ThreadPool Core                 │
│   - dispatchTasksToBackground()         │
│   - waitForCompletion()                 │
└─────────────────────────────────────────┘
                    │
                    ▼
┌──────────────────────────────────────────────────────┐
│         Laravel Queue Workers                        │
├──────────────────────────────────────────────────────┤
│ Worker 1 │ Worker 2 │ Worker 3 │ Worker 4 │ Worker 5 │
│ Queue 1  │ Queue 2  │ Queue 3  │ Queue 4  │ Queue 5  │
│ Job A    │ Job B    │ Job C    │ Job D    │ Job E    │
└──────────────────────────────────────────────────────┘
                    │
                    ▼ (Concurrent Processing)
┌─────────────────────────────────────────┐
│         ThreadPoolJob.php               │
│   - processEmailNotification()          │
│   - processPayment()                    │
│   - processLocationUpdate()             │
│   - processFareCalculation()            │
│   - processPushNotification()           │
└─────────────────────────────────────────┘
                    │
                    ▼
┌─────────────────────────────────────────┐
│         Cache Storage                   │
│   - Results tracking                    │
│   - Progress monitoring                 │
└─────────────────────────────────────────┘
```

## Chi tiết xử lý các file

### 1. **DemoThreadPoolCommand.php**
```php
// Command được gọi bởi: php artisan demo:thread-pool --workers=5
public function handle(): int
{
    $workers = $this->getWorkerCount();           // Lấy số workers từ option
    $this->info("🚀 Starting ThreadPool Demo");

    $manager = new ThreadPoolManager($workers);   // Tạo manager với workers
    $manager->demonstrateConcurrentProcessing();  // Bắt đầu demo

    return self::SUCCESS;
}
```

### 2. **ThreadPoolManager.php**
```php
// Điều phối và submit tasks
public function demonstrateConcurrentProcessing(): void
{
    $this->threadPool->start();                   // Khởi tạo thread pool

    // Submit từng loại task (15 tasks total)
    $this->submitEmailNotificationTasks();       // 3 email tasks
    $this->submitPaymentProcessingTasks();       // 3 payment tasks
    $this->submitLocationUpdateTasks();          // 3 location tasks
    $this->submitFareCalculationTasks();         // 3 fare tasks
    $this->submitPushNotificationTasks();        // 3 push tasks

    $this->threadPool->processTasks();           // Dispatch tất cả tasks
    $this->threadPool->stop();                   // Dừng thread pool
}
```

### 3. **ThreadPool.php**
```php
// Xử lý dispatching và monitoring
public function processTasks(): void
{
    // Dispatch tasks to background workers
    $this->dispatchTasksToBackground();

    // Wait for completion với real-time monitoring
    $this->waitForCompletion($taskCount);

    // Display statistics
    $this->displayStatistics($totalTime);
}

private function dispatchTasksToBackground(): void
{
    // Round-robin distribution
    foreach ($this->taskQueue as $index => $task) {
        $workerId = ($index % $this->maxWorkers) + 1;  // Worker 1,2,3,4,5

        // Dispatch job to specific worker queue
        ThreadPoolJob::dispatch($task['name'], $task['data'], $workerId, $this->poolId);
    }
}
```

### 4. **ThreadPoolJob.php** - Background Worker
```php
// Background job xử lý concurrent trong Laravel Queue
public function handle(): void
{
    // Xử lý task dựa trên type
    $result = $this->processTask();

    // Store result vào cache cho progress tracking
    $this->storeResult($result);
}

private function processTask(): array
{
    // Switch case xử lý từng loại task
    return match ($this->taskName) {
        'email_notification' => $this->processEmailNotification(),
        'payment_processing' => $this->processPayment(),
        'location_update' => $this->processLocationUpdate(),
        'fare_calculation' => $this->processFareCalculation(),
        'push_notification' => $this->processPushNotification(),
    };
}
```

### 5. **Cache System** - Progress Tracking
```php
// Results được store trong cache với key: threadpool_results_{poolId}
private function storeResult(array $result): void
{
    $cacheKey = "threadpool_results_{$this->poolId}";
    $results = Cache::get($cacheKey, []);
    $results[] = $result;
    Cache::put($cacheKey, $results, 3600);
}
```

## Command

### Demo Command
```bash
# Run with default 5 workers
php artisan demo:thread-pool

# Run with custom worker count
php artisan demo:thread-pool --workers=3
php artisan demo:thread-pool --workers=8
```

### Queue Worker
**Quan trọng**: Cần chạy queue worker để xử lý background jobs
```bash
# Start queue worker
php artisan queue:work

# Start multiple workers
php artisan queue:work --queue=worker_1 &
php artisan queue:work --queue=worker_2 &
php artisan queue:work --queue=worker_3 &
```
## Configuration

### Queue Configuration
Cấu hình queue trong `config/queue.php`:

```php
'connections' => [
    'redis' => [
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
    ],
],
```

### Environment Variables
```env
QUEUE_CONNECTION=redis
```

## Output

### Console Output Step-by-Step
```bash
# Bước 1: User chạy command
$ php artisan demo:thread-pool --workers=5

# Bước 2: DemoThreadPoolCommand khởi tạo
🚀 Starting ThreadPool Demo with 5 workers

# Bước 3: ThreadPoolManager bắt đầu demo
=== DEMO THREAD POOL - CONCURRENT BACKGROUND PROCESSING ===

# Bước 4: ThreadPool khởi tạo
🚀 ThreadPool started with 5 workers (Background Processing)
   - Pool ID: pool_65a1b2c3d4e5f
   - Processing mode: Laravel Queue Background Jobs

# Bước 5: ThreadPoolManager submit tasks
📋 Submitting real-world workload tasks...

📝 Task 'email_notification' added to queue
📝 Task 'email_notification' added to queue
📝 Task 'email_notification' added to queue
📝 Task 'payment_processing' added to queue
📝 Task 'payment_processing' added to queue
📝 Task 'payment_processing' added to queue
📝 Task 'location_update' added to queue
📝 Task 'location_update' added to queue
📝 Task 'location_update' added to queue
📝 Task 'fare_calculation' added to queue
📝 Task 'fare_calculation' added to queue
📝 Task 'fare_calculation' added to queue
📝 Task 'push_notification' added to queue
📝 Task 'push_notification' added to queue
📝 Task 'push_notification' added to queue

# Bước 6: ThreadPool dispatch tasks
⚡ Dispatching 15 tasks to 5 workers in background

📋 Dispatching task 'email_notification' to Worker 1
📋 Dispatching task 'email_notification' to Worker 2
📋 Dispatching task 'email_notification' to Worker 3
📋 Dispatching task 'payment_processing' to Worker 4
📋 Dispatching task 'payment_processing' to Worker 5
📋 Dispatching task 'payment_processing' to Worker 1
📋 Dispatching task 'location_update' to Worker 2
📋 Dispatching task 'location_update' to Worker 3
📋 Dispatching task 'location_update' to Worker 4
📋 Dispatching task 'fare_calculation' to Worker 5
📋 Dispatching task 'fare_calculation' to Worker 1
📋 Dispatching task 'fare_calculation' to Worker 2
📋 Dispatching task 'push_notification' to Worker 3
📋 Dispatching task 'push_notification' to Worker 4
📋 Dispatching task 'push_notification' to Worker 5

🚀 All tasks dispatched to background workers!
💫 Workers are now processing tasks concurrently in background...

# Bước 7: Background workers bắt đầu xử lý (concurrent)
# Round 1 - All workers start simultaneously
🔄 Worker 1 starting task: email_notification
🔄 Worker 2 starting task: email_notification
🔄 Worker 3 starting task: email_notification
🔄 Worker 4 starting task: payment_processing
🔄 Worker 5 starting task: payment_processing
✅ Worker 1 completed: email_notification (2.134s)
✅ Worker 2 completed: email_notification (1.875s)
✅ Worker 3 completed: email_notification (2.456s)
✅ Worker 4 completed: payment_processing (1.234s)
✅ Worker 5 completed: payment_processing (1.678s)

# Round 2 - Workers pick up next tasks
🔄 Worker 1 starting task: payment_processing
🔄 Worker 2 starting task: location_update
🔄 Worker 3 starting task: location_update
🔄 Worker 4 starting task: location_update
🔄 Worker 5 starting task: fare_calculation
✅ Worker 1 completed: payment_processing (1.567s)
✅ Worker 2 completed: location_update (2.123s)
✅ Worker 3 completed: location_update (1.890s)
✅ Worker 4 completed: location_update (2.234s)
✅ Worker 5 completed: fare_calculation (1.756s)

# Round 3 - Final tasks
🔄 Worker 1 starting task: fare_calculation
🔄 Worker 2 starting task: fare_calculation
🔄 Worker 3 starting task: push_notification
🔄 Worker 4 starting task: push_notification
🔄 Worker 5 starting task: push_notification
✅ Worker 1 completed: fare_calculation (1.890s)
✅ Worker 2 completed: fare_calculation (2.045s)
✅ Worker 3 completed: push_notification (1.567s)
✅ Worker 4 completed: push_notification (1.789s)
✅ Worker 5 completed: push_notification (2.123s)

# Bước 8: Waiting for completion
⏳ Waiting for background tasks to complete...
🎉 All 15 tasks completed!

# Bước 9: Completion và statistics
✅ All tasks completed in 3.245s

📊 Statistics:
   - Total tasks: 15
   - Completed tasks: 15
   - Failed tasks: 0
   - Total processing time: 3.245s
   - Workers used: 5

👥 Worker Distribution:
   - Worker 1: 3 tasks
   - Worker 2: 3 tasks
   - Worker 3: 3 tasks
   - Worker 4: 3 tasks
   - Worker 5: 3 tasks
   - Average execution time: 1.856s per task

==================================================
📊 DETAILED STATISTICS
==================================================
Pool Information:
  - Pool ID: pool_65a1b2c3d4e5f
  - Max Workers: 5
  - Processing Mode: Laravel Queue Background Jobs

Task Results:
  - Total Tasks: 15
  - Completed: 15
  - Failed: 0
  - Success Rate: 100.0%

==================================================

🛑 ThreadPool stopped

# Bước 10: Command completion
✅ Demo completed successfully!
```
