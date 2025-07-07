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
    protected $signature = 'demo:thread-pool {--workers=5 : Number of workers}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo ThreadPool with concurrent background processing';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $workers = $this->getWorkerCount();

        $this->info("🚀 Starting ThreadPool Demo with {$workers} workers");

        $manager = new ThreadPoolManager($workers);
        $manager->demonstrateConcurrentProcessing();

        $this->info('✅ Demo completed successfully!');

        return self::SUCCESS;
    }

    /**
     * Get worker count from option
     */
    private function getWorkerCount(): int
    {
        return (int) $this->option('workers');
    }
}
