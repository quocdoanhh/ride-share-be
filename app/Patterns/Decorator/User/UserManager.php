<?php

namespace App\Patterns\Decorator\User;

/**
 * User Manager
 */
class UserManager
{
    /**
     * Demonstrate different user types
     */
    public function demonstrateUserTypes(): void
    {
        echo "=== DEMO DECORATOR PATTERN - USER MANAGEMENT ===\n\n";

        // 1. Basic User
        $basicUser = new BasicUser('John Doe', 'john.doe@example.com');
        $this->displayUser($basicUser, 'Basic User');

        // 2. Driver
        $driver = new DriverDecorator(new BasicUser('Jane Smith', 'jane.smith@example.com'));
        $this->displayUser($driver, 'Driver');

        // 3. Passenger
        $passenger = new PassengerDecorator(new BasicUser('Mike Johnson', 'mike.johnson@example.com'));
        $this->displayUser($passenger, 'Passenger');

        // 4. Premium Passenger
        $premiumPassenger = new PremiumDecorator(
            new PassengerDecorator(new BasicUser('Sarah Brown', 'sarah.brown@example.com'))
        );
        $this->displayUser($premiumPassenger, 'Premium Passenger');

        // 5. Verified Driver
        $verifiedDriver = new VerifiedDecorator(
            new DriverDecorator(new BasicUser('David Lee', 'david.lee@example.com'))
        );
        $this->displayUser($verifiedDriver, 'Verified Driver');

        // 6. Admin
        $admin = new AdminDecorator(new BasicUser('Admin System', 'admin@rideshare.com'));
        $this->displayUser($admin, 'Admin');

        // 7. Premium Verified Driver
        $premiumVerifiedDriver = new PremiumDecorator(
            new VerifiedDecorator(
                new DriverDecorator(new BasicUser('Emily Chen', 'emily.chen@example.com'))
            )
        );
        $this->displayUser($premiumVerifiedDriver, 'Premium Verified Driver');

        echo "\n=== TEST ACCESS PERMISSIONS ===\n";
        $this->testPermissions($premiumVerifiedDriver);
    }

    /**
     * Create custom user with custom roles
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
     * Test access permissions of user
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
        echo "Access permissions:\n";

        foreach ($resources as $resource) {
            $canAccess = $user->canAccess($resource) ? '✓' : '✗';
            echo "  {$canAccess} {$resource}\n";
        }
        echo "\n";
    }

    /**
     * Display user information
     */
    private function displayUser(UserInterface $user, string $title): void
    {
        echo "--- {$title} ---\n";
        echo "Display name: " . $user->getDisplayName() . "\n";

        $info = $user->getInfo();
        echo "Email: " . $info['email'] . "\n";
        echo "Type: " . ($info['type'] ?? 'basic_user') . "\n";

        if (isset($info['role'])) {
            echo "Role: " . $info['role'] . "\n";
        }

        if (isset($info['subscription'])) {
            echo "Subscription: " . $info['subscription'] . "\n";
        }

        if (isset($info['verified'])) {
            echo "Verified: Yes (" . $info['verification_date'] . ")\n";
        }

        echo "Number of permissions: " . count($user->getPermissions()) . "\n";
        echo "---\n\n";
    }

    /**
     * Demo user upgrade workflow
     */
    public function demonstrateUserUpgrade(): void
    {
        echo "=== USER UPGRADE WORKFLOW ===\n\n";

        // Start with basic user
        $user = new BasicUser('New User', 'newuser@example.com');
        echo "1. New user registered:\n";
        $this->displayUser($user, 'New User');

        // Upgrade to passenger
        $user = new PassengerDecorator($user);
        echo "2. After registering as passenger:\n";
        $this->displayUser($user, 'Passenger');

        // Upgrade to premium
        $user = new PremiumDecorator($user);
        echo "3. After upgrading to Premium:\n";


        // Verify account
        $user = new VerifiedDecorator($user);
        echo "4. After verifying account:\n";
        $this->displayUser($user, 'Premium Passenger Verified');

        echo "\n=== ADVANTAGES OF DECORATOR PATTERN ===\n";
        echo "1. Flexible: Can add/remove permissions without affecting current code\n";
        echo "2. Extensible: Easy to add new roles (moderator, vip, etc.)\n";
        echo "3. Combination: Can combine multiple roles for a single user\n";
        echo "4. Maintenance: Each decorator only manages one type of permission\n";
    }
}