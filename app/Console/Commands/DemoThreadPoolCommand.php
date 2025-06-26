<?php

namespace App\Console\Commands;

use App\Patterns\ThreadPool\ThreadPoolManager;
use Illuminate\Console\Command;

class DemoThreadPoolCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:thread-pool
                            {--type=basic : Demo type (basic, concurrent, error, real-world, performance)}
                            {--workers=3 : Number of workers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo Thread Pool Pattern với xử lý tác vụ bất đồng bộ';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $type = $this->option('type');
        $workers = $this->option('workers');

        $manager = new ThreadPoolManager($workers);

        echo "=== DEMO THREAD POOL PATTERN - XỬ LÝ TÁC VỤ BẤT ĐỒNG BỘ ===\n\n";

        switch ($type) {
            case 'basic':
                $manager->demonstrateBasicUsage();
                break;

            case 'concurrent':
                $manager->demonstrateConcurrentProcessing();
                break;

            case 'error':
                $manager->demonstrateErrorHandling();
                break;

            case 'real-world':
                $manager->demonstrateRealWorldWorkload();
                break;

            case 'performance':
                $manager->demonstratePerformanceComparison();
                break;

            default:
                $this->demoAllTypes($manager);
                break;
        }

        $this->newLine();
        $this->info('Demo hoàn thành!');
        $this->line('Các lệnh có thể sử dụng:');
        $this->line('php artisan demo:thread-pool --type=basic --workers=3');
        $this->line('php artisan demo:thread-pool --type=concurrent --workers=5');
        $this->line('php artisan demo:thread-pool --type=error --workers=2');
        $this->line('php artisan demo:thread-pool --type=real-world --workers=4');
        $this->line('php artisan demo:thread-pool --type=performance');
    }

    /**
     * Demo tất cả các loại
     */
    private function demoAllTypes(ThreadPoolManager $manager): void
    {
        echo "🎯 DEMO TẤT CẢ CÁC LOẠI THREAD POOL\n";
        echo "===================================\n\n";

        // 1. Basic usage
        echo "1️⃣ CƠ BẢN:\n";
        $manager->demonstrateBasicUsage();
        echo "\n";

        // 2. Concurrent processing
        echo "2️⃣ XỬ LÝ ĐỒNG THỜI:\n";
        $manager->demonstrateConcurrentProcessing();
        echo "\n";

        // 3. Error handling
        echo "3️⃣ XỬ LÝ LỖI:\n";
        $manager->demonstrateErrorHandling();
        echo "\n";

        // 4. Real-world workload
        echo "4️⃣ WORKLOAD THỰC TẾ:\n";
        $manager->demonstrateRealWorldWorkload();
        echo "\n";

        // 5. Performance comparison
        echo "5️⃣ SO SÁNH HIỆU SUẤT:\n";
        $manager->demonstratePerformanceComparison();
        echo "\n";

        echo "🎉 HOÀN THÀNH DEMO TẤT CẢ CÁC LOẠI!\n";
    }
}