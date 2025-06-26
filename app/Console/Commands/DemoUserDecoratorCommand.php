<?php

namespace App\Console\Commands;

use App\Patterns\Decorator\User\UserManager;
use Illuminate\Console\Command;

class DemoUserDecoratorCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'demo:user-decorator
                            {--type=all : Demo type (all, upgrade, custom)}
                            {--name= : Custom user name}
                            {--email= : Custom user email}
                            {--roles=* : Custom user roles}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Demo Decorator Pattern với quản lý User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userManager = new UserManager();
        $type = $this->option('type');

        switch ($type) {
            case 'upgrade':
                $userManager->demonstrateUserUpgrade();
                break;

            case 'custom':
                $this->handleCustomUser($userManager);
                break;

            default:
                $userManager->demonstrateUserTypes();
                break;
        }

        $this->newLine();
        $this->info('Demo hoàn thành!');
        $this->line('Các lệnh có thể sử dụng:');
        $this->line('php artisan demo:user-decorator --type=upgrade');
        $this->line('php artisan demo:user-decorator --type=custom --name="Nguyễn Văn A" --email="test@email.com" --roles=driver --roles=premium');
    }

    /**
     * Xử lý tạo user tùy chỉnh
     */
    private function handleCustomUser(UserManager $userManager): void
    {
        $name = $this->option('name') ?: 'User Test';
        $email = $this->option('email') ?: 'test@email.com';
        $roles = $this->option('roles') ?: [];

        $this->info("Tạo user tùy chỉnh:");
        $this->line("Tên: {$name}");
        $this->line("Email: {$email}");
        $this->line("Roles: " . implode(', ', $roles));
        $this->newLine();

        $user = $userManager->createCustomUser($name, $email, $roles);

        echo "=== THÔNG TIN USER ===\n";
        echo "Tên hiển thị: " . $user->getDisplayName() . "\n";

        $info = $user->getInfo();
        echo "Email: " . $info['email'] . "\n";
        echo "Loại: " . ($info['type'] ?? 'basic_user') . "\n";

        if (isset($info['role'])) {
            echo "Vai trò: " . $info['role'] . "\n";
        }

        if (isset($info['subscription'])) {
            echo "Gói: " . $info['subscription'] . "\n";
        }

        if (isset($info['verified'])) {
            echo "Xác thực: Có (" . $info['verification_date'] . ")\n";
        }

        echo "Số quyền: " . count($user->getPermissions()) . "\n";
        echo "Danh sách quyền: " . implode(', ', $user->getPermissions()) . "\n";

        $this->newLine();
        $userManager->testPermissions($user);
    }
}