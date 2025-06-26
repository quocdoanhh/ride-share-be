<?php

namespace App\Patterns\Decorator\User;

/**
 * User Manager - Demo class để sử dụng Decorator Pattern
 */
class UserManager
{
    /**
     * Demo các loại user khác nhau
     */
    public function demonstrateUserTypes(): void
    {
        echo "=== DEMO DECORATOR PATTERN - QUẢN LÝ USER ===\n\n";

        // 1. User cơ bản
        $basicUser = new BasicUser('Nguyễn Văn A', 'nguyenvana@email.com');
        $this->displayUser($basicUser, 'User cơ bản');

        // 2. Driver
        $driver = new DriverDecorator(new BasicUser('Trần Thị B', 'tranthib@email.com'));
        $this->displayUser($driver, 'Tài xế');

        // 3. Passenger
        $passenger = new PassengerDecorator(new BasicUser('Lê Văn C', 'levanc@email.com'));
        $this->displayUser($passenger, 'Hành khách');

        // 4. Premium Passenger
        $premiumPassenger = new PremiumDecorator(
            new PassengerDecorator(new BasicUser('Phạm Thị D', 'phamthid@email.com'))
        );
        $this->displayUser($premiumPassenger, 'Hành khách Premium');

        // 5. Verified Driver
        $verifiedDriver = new VerifiedDecorator(
            new DriverDecorator(new BasicUser('Hoàng Văn E', 'hoangvane@email.com'))
        );
        $this->displayUser($verifiedDriver, 'Tài xế đã xác thực');

        // 6. Admin
        $admin = new AdminDecorator(new BasicUser('Admin System', 'admin@rideshare.com'));
        $this->displayUser($admin, 'Quản trị viên');

        // 7. Premium Verified Driver
        $premiumVerifiedDriver = new PremiumDecorator(
            new VerifiedDecorator(
                new DriverDecorator(new BasicUser('Vũ Thị F', 'vuthif@email.com'))
            )
        );
        $this->displayUser($premiumVerifiedDriver, 'Tài xế Premium đã xác thực');

        echo "\n=== KIỂM TRA QUYỀN TRUY CẬP ===\n";
        $this->testPermissions($premiumVerifiedDriver);
    }

    /**
     * Tạo user với các role tùy chỉnh
     */
    public function createCustomUser(string $name, string $email, array $roles = []): UserInterface
    {
        $user = new BasicUser($name, $email);

        foreach ($roles as $role) {
            switch (strtolower($role)) {
                case 'driver':
                    $user = new DriverDecorator($user);
                    break;
                case 'passenger':
                    $user = new PassengerDecorator($user);
                    break;
                case 'admin':
                    $user = new AdminDecorator($user);
                    break;
                case 'premium':
                    $user = new PremiumDecorator($user);
                    break;
                case 'verified':
                    $user = new VerifiedDecorator($user);
                    break;
            }
        }

        return $user;
    }

    /**
     * Kiểm tra quyền truy cập của user
     */
    public function testPermissions(UserInterface $user): void
    {
        $resources = [
            'profile:read',
            'trip:create',
            'trip:request',
            'user:manage',
            'priority:booking',
            'system:config'
        ];

        echo "User: " . $user->getDisplayName() . "\n";
        echo "Quyền truy cập:\n";

        foreach ($resources as $resource) {
            $canAccess = $user->canAccess($resource) ? '✓' : '✗';
            echo "  {$canAccess} {$resource}\n";
        }
        echo "\n";
    }

    /**
     * Hiển thị thông tin user
     */
    private function displayUser(UserInterface $user, string $title): void
    {
        echo "--- {$title} ---\n";
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
        echo "---\n\n";
    }

    /**
     * Demo workflow thăng cấp user
     */
    public function demonstrateUserUpgrade(): void
    {
        echo "=== WORKFLOW THĂNG CẤP USER ===\n\n";

        // Bắt đầu với user cơ bản
        $user = new BasicUser('Nguyễn Văn Mới', 'newuser@email.com');
        echo "1. User mới đăng ký:\n";
        $this->displayUser($user, 'User mới');

        // Thăng cấp thành passenger
        $user = new PassengerDecorator($user);
        echo "2. Sau khi đăng ký làm hành khách:\n";
        $this->displayUser($user, 'Hành khách');

        // Thăng cấp thành premium
        $user = new PremiumDecorator($user);
        echo "3. Sau khi nâng cấp lên Premium:\n";
        $this->displayUser($user, 'Premium Passenger');

        // Xác thực tài khoản
        $user = new VerifiedDecorator($user);
        echo "4. Sau khi xác thực tài khoản:\n";
        $this->displayUser($user, 'Premium Passenger đã xác thực');

        echo "\n=== ƯU ĐIỂM CỦA DECORATOR PATTERN ===\n";
        echo "1. Linh hoạt: Có thể thêm/bớt quyền mà không ảnh hưởng code hiện tại\n";
        echo "2. Mở rộng: Dễ dàng thêm role mới (moderator, vip, etc.)\n";
        echo "3. Kết hợp: Có thể kết hợp nhiều role cho một user\n";
        echo "4. Bảo trì: Mỗi decorator chỉ quản lý một loại quyền\n";
    }
}