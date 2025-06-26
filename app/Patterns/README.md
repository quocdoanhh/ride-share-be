# Design Patterns - Tổng hợp

Dự án này chứa các ví dụ về Design Patterns được áp dụng trong hệ thống Ride-Share.

## 📁 Cấu trúc thư mục

```
app/Patterns/
├── Decorator/
│   └── User/                         # Ví dụ quản lý user
│       ├── UserInterface.php         # Interface và BasicUser
│       ├── UserDecorator.php         # Các decorators cho user
│       ├── UserManager.php           # Demo class
│       └── README.md                 # Documentation
├── Facade/
│   ├── TripBookingFacade.php         # Facade cho đặt chuyến đi
│   ├── Services/                     # Các service classes
│   │   ├── UserService.php
│   │   ├── PaymentService.php
│   │   ├── DriverService.php
│   │   ├── NotificationService.php
│   │   └── TripService.php
│   └── README.md                     # Documentation
└── ThreadPool/
    ├── ThreadPool.php                # Thread Pool implementation
    ├── Tasks/                        # Các task classes
    │   ├── EmailNotificationTask.php
    │   ├── PaymentProcessingTask.php
    │   ├── LocationUpdateTask.php
    │   ├── FareCalculationTask.php
    │   ├── PushNotificationTask.php
    │   └── DataBackupTask.php
    ├── ThreadPoolManager.php         # Demo class
    └── README.md                     # Documentation
```

## 🎯 Các Patterns đã implement

### 1. Decorator Pattern
**Mục đích**: Thêm chức năng mới vào đối tượng mà không làm thay đổi cấu trúc của nó.

**Ví dụ**: **User Management** - Thêm vai trò và tính năng cho user:
- **BasicUser**: User cơ bản với quyền tối thiểu
- **DriverDecorator**: Thêm vai trò tài xế
- **PassengerDecorator**: Thêm vai trò hành khách
- **AdminDecorator**: Thêm vai trò quản trị viên
- **PremiumDecorator**: Thêm gói premium
- **VerifiedDecorator**: Thêm trạng thái xác thực

**Chạy demo**:
```bash
# Demo cơ bản
php artisan demo:user-decorator

# Demo workflow thăng cấp user
php artisan demo:user-decorator --type=upgrade

# Tạo user tùy chỉnh
php artisan demo:user-decorator --type=custom --name="Nguyễn Văn A" --roles=driver --roles=premium
```

### 2. Facade Pattern
**Mục đích**: Cung cấp interface đơn giản cho các subsystem phức tạp.

**Ví dụ**: TripBookingFacade đơn giản hóa việc đặt chuyến đi bằng cách:
- Xác thực user
- Xử lý thanh toán
- Tìm tài xế
- Tạo chuyến đi
- Gửi thông báo

**Chạy demo**:
```bash
php artisan demo:facade --action=book
php artisan demo:facade --action=cancel --trip-id=1 --user-id=1
php artisan demo:facade --action=track --trip-id=1
php artisan demo:facade --action=rate --trip-id=1 --user-id=1 --rating=5
```

### 3. Thread Pool Pattern
**Mục đích**: Quản lý pool các worker threads để xử lý tác vụ bất đồng bộ.

**Ví dụ**: Xử lý đồng thời các tác vụ:
- Gửi email thông báo
- Xử lý thanh toán
- Cập nhật vị trí tài xế
- Tính toán cước phí
- Gửi push notification
- Backup dữ liệu

**Chạy demo**:
```bash
php artisan demo:thread-pool --type=basic --workers=3
php artisan demo:thread-pool --type=concurrent --workers=5
php artisan demo:thread-pool --type=error --workers=2
php artisan demo:thread-pool --type=real-world --workers=4
php artisan demo:thread-pool --type=performance
```

## 🚀 Cách sử dụng

### 1. Chạy tất cả demos
```bash
# Decorator Pattern
php artisan demo:user-decorator

# Facade Pattern
php artisan demo:facade

# Thread Pool Pattern
php artisan demo:thread-pool
```

### 2. Sử dụng trong code
```php
// Decorator Pattern
use App\Patterns\Decorator\User\*;
$user = new BasicUser('Nguyễn Văn A', 'user@email.com');
$driver = new DriverDecorator($user);
$premiumDriver = new PremiumDecorator($driver);

// Facade Pattern
use App\Patterns\Facade\TripBookingFacade;
$facade = new TripBookingFacade();
$result = $facade->bookTrip($bookingData);

// Thread Pool Pattern
use App\Patterns\ThreadPool\ThreadPoolManager;
$manager = new ThreadPoolManager(5);
$manager->demonstrateRealWorldWorkload();
```

## 📊 So sánh các Patterns

| Pattern | Mục đích | Ưu điểm | Nhược điểm | Ứng dụng |
|---------|----------|---------|------------|----------|
| **Decorator** | Thêm chức năng linh hoạt | Linh hoạt, mở rộng dễ | Có thể phức tạp với nhiều decorator | User roles, logging, caching |
| **Facade** | Đơn giản hóa interface | Dễ sử dụng, giảm coupling | Có thể tạo dependency | API endpoints, service layers |
| **Thread Pool** | Xử lý tác vụ bất đồng bộ | Hiệu suất cao, kiểm soát tài nguyên | Phức tạp, khó debug | Background jobs, async processing |

## 🎯 Ứng dụng thực tế trong Ride-Share

### Decorator Pattern
- **User Management**: Kết hợp nhiều vai trò (driver + premium + verified)
- **Service Enhancement**: Thêm logging, caching, validation cho services
- **Feature Toggles**: Bật/tắt tính năng theo gói dịch vụ

### Facade Pattern
- **API Controllers**: Đơn giản hóa việc gọi nhiều services
- **Service Integration**: Tích hợp payment, notification, location services
- **Client SDK**: Cung cấp interface đơn giản cho mobile app

### Thread Pool Pattern
- **Background Jobs**: Xử lý email, notification, payment
- **Real-time Updates**: Cập nhật vị trí tài xế, tracking
- **Data Processing**: Backup, analytics, reporting

## 🔧 Cài đặt và chạy

1. **Clone repository**:
```bash
git clone <repository-url>
cd ride-share-be
```

2. **Cài đặt dependencies**:
```bash
composer install
```

3. **Chạy demos**:
```bash
# Tất cả patterns
php artisan demo:user-decorator
php artisan demo:facade
php artisan demo:thread-pool
```

## 📚 Tài liệu tham khảo

- [Design Patterns - Gang of Four](https://en.wikipedia.org/wiki/Design_Patterns)
- [Laravel Design Patterns](https://laravel.com/docs/design-patterns)
- [PHP Design Patterns](https://refactoring.guru/design-patterns/php)
