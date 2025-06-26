# Decorator Pattern - Ví dụ Quản lý User

## Giới thiệu

Ví dụ này minh họa Decorator Pattern trong việc quản lý user với các vai trò và quyền khác nhau trong hệ thống ride-share.

## Cấu trúc

```
UserInterface (Component)
    ↑
BasicUser (Concrete Component)
    ↑
UserDecorator (Abstract Decorator)
    ↑
┌─────────────────────────────────────────────┐
│  DriverDecorator    PassengerDecorator      │
│  AdminDecorator     PremiumDecorator        │
│  VerifiedDecorator                          │
└─────────────────────────────────────────────┘
```

## Các thành phần

### 1. Component Interface (`UserInterface`)
- Định nghĩa interface chung cho tất cả user
- Methods: `getInfo()`, `getPermissions()`, `canAccess()`, `getDisplayName()`

### 2. Concrete Component (`BasicUser`)
- User cơ bản với quyền tối thiểu
- Quyền cơ bản: `profile:read`, `profile:update`

### 3. Abstract Decorator (`UserDecorator`)
- Class trừu tượng chứa reference đến UserInterface
- Implement các method cơ bản

### 4. Concrete Decorators

#### DriverDecorator
- **Vai trò**: Tài xế
- **Quyền thêm**: `trip:create`, `trip:accept`, `trip:complete`, `location:update`, `earnings:view`
- **Hiển thị**: Tên + "(Tài xế)"

#### PassengerDecorator
- **Vai trò**: Hành khách
- **Quyền thêm**: `trip:request`, `trip:rate`, `payment:manage`, `history:view`
- **Hiển thị**: Tên + "(Hành khách)"

#### AdminDecorator
- **Vai trò**: Quản trị viên
- **Quyền thêm**: `user:manage`, `trip:monitor`, `system:config`, `reports:view`, `support:manage`
- **Hiển thị**: Tên + "(Admin)"

#### PremiumDecorator
- **Gói**: Premium
- **Quyền thêm**: `priority:booking`, `premium:support`, `discount:apply`, `premium:features`
- **Hiển thị**: "⭐" + Tên

#### VerifiedDecorator
- **Trạng thái**: Đã xác thực
- **Thông tin thêm**: `verified: true`, `verification_date`
- **Hiển thị**: Tên + "✓"

## Cách sử dụng

### Chạy demo đầy đủ:
```bash
php artisan demo:user-decorator
```

### Demo workflow thăng cấp user:
```bash
php artisan demo:user-decorator --type=upgrade
```

### Tạo user tùy chỉnh:
```bash
# Tài xế premium đã xác thực
php artisan demo:user-decorator --type=custom \
    --name="Nguyễn Văn A" \
    --email="driver@email.com" \
    --roles=driver --roles=premium --roles=verified

# Hành khách thường
php artisan demo:user-decorator --type=custom \
    --name="Trần Thị B" \
    --email="passenger@email.com" \
    --roles=passenger

# Admin
php artisan demo:user-decorator --type=custom \
    --name="Admin" \
    --email="admin@rideshare.com" \
    --roles=admin
```

### Sử dụng trong code:
```php
use App\Patterns\Decorator\User\*;

// Tạo user cơ bản
$user = new BasicUser('Nguyễn Văn A', 'user@email.com');

// Thêm vai trò tài xế
$driver = new DriverDecorator($user);

// Thêm gói premium
$premiumDriver = new PremiumDecorator($driver);

// Xác thực tài khoản
$verifiedPremiumDriver = new VerifiedDecorator($premiumDriver);

// Kiểm tra quyền
if ($verifiedPremiumDriver->canAccess('trip:create')) {
    echo "Có thể tạo chuyến đi";
}

// Sử dụng UserManager
$userManager = new UserManager();
$customUser = $userManager->createCustomUser(
    'Test User',
    'test@email.com',
    ['driver', 'premium', 'verified']
);
```

## Ví dụ kết hợp nhiều decorator

```php
// Tài xế premium đã xác thực
$user = new BasicUser('Nguyễn Văn A', 'driver@email.com');
$user = new DriverDecorator($user);        // Thêm vai trò tài xế
$user = new PremiumDecorator($user);       // Thêm gói premium
$user = new VerifiedDecorator($user);      // Xác thực tài khoản

// Kết quả: ⭐ Nguyễn Văn A (Tài xế) ✓
echo $user->getDisplayName();

// Quyền: profile:read, profile:update, trip:create, trip:accept,
//        trip:complete, location:update, earnings:view,
//        priority:booking, premium:support, discount:apply, premium:features
print_r($user->getPermissions());
```

## Ứng dụng thực tế trong Ride-Share

### 1. Quản lý vai trò linh hoạt
- User có thể vừa là tài xế vừa là hành khách
- Dễ dàng thêm/bớt vai trò mà không ảnh hưởng code

### 2. Hệ thống gói dịch vụ
- Basic, Premium, VIP packages
- Mỗi gói có quyền và tính năng riêng

### 3. Xác thực và bảo mật
- Verified users có thêm quyền
- KYC (Know Your Customer) levels

### 4. Quản lý quyền truy cập
- Middleware kiểm tra quyền
- API endpoints protection

## Ưu điểm

1. **Linh hoạt**: Có thể kết hợp nhiều vai trò cho một user
2. **Mở rộng**: Dễ dàng thêm vai trò mới (moderator, vip, etc.)
3. **Bảo trì**: Mỗi decorator chỉ quản lý một loại quyền
4. **Tái sử dụng**: Có thể dùng lại decorator cho nhiều user
5. **Single Responsibility**: Mỗi decorator có trách nhiệm riêng biệt

## So sánh với các pattern khác

### vs Strategy Pattern
- **Decorator**: Thêm chức năng vào đối tượng
- **Strategy**: Thay đổi hành vi của đối tượng

### vs Composite Pattern
- **Decorator**: Một đối tượng với nhiều wrapper
- **Composite**: Nhiều đối tượng trong cấu trúc cây

### vs Chain of Responsibility
- **Decorator**: Tất cả decorator đều được thực thi
- **Chain**: Chỉ một handler xử lý request