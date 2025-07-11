# Stub và Spy

## Khái niệm và Mục đích sử dụng

### Stub

#### Khái niệm:
Stub là một kỹ thuật trong unit test dùng để mô phỏng hành vi
của một thành phần phụ thuộc, thường là để trả về giá trị cố
định, không quan tâm đến cách nó được gọi, bao nhiêu lần gọi,
hay tham số truyền vào.

#### Khi nào dùng Stub?
- Khi không cần kiểm tra hành vi của thành phần phụ thuộc,
chỉ cần nó trả về đúng dữ liệu.
- Khi muốn test logic bên trong class chính, nhưng không
muốn kết nối thật đến DB, API, etc.

### Spy

#### Khái niệm:
Spy là một kỹ thuật trong unit test dùng để theo dõi và ghi
nhận hành vi của một function/thành phần thực, mà vẫn giữ
nguyên logic gốc của nó.
Khác với Stub hay Mock, Spy không thay đổi kết quả thực thi,
mà chỉ quan sát xem function được gọi như thế nào.

#### Khi nào sử dụng Spy?
- Xác minh function đã được gọi bao nhiêu lần.
- Kiểm tra function được gọi với tham số nào.
- Đảm bảo flow logic thực sự chạy đúng luồng, nhưng vẫn
có thể test hành vi.

## So sánh Stub vs Spy

| Tiêu chí | Stub | Spy |
|----------|------|-----|
| **Mục đích** | Cung cấp dữ liệu giả | Theo dõi và verify calls |
| **Use case** | Test business logic | Test interactions |
| **Complexity** | Đơn giản hơn | Phức tạp hơn |
| **Maintenance** | Dễ maintain | Cần chú ý khi refactor |

## Cấu trúc thư mục

```
tests/Unit/
├── Stub/
│   ├── TripServiceStubTest.php
│   └── AuthServiceStubTest.php
├── Spy/
│   ├── TripServiceSpyTest.php
│   └── TripControllerSpyTest.php
└── README.md
```

## Các file test đã tạo

### Folder Stub

#### AuthServiceStubTest.php
- `login()` - Đăng nhập bằng số điện thoại
- `verifyLogin()` - Xác thực mã OTP

### Folder Spy

#### TripControllerSpyTest.php
- Service method calls với đúng parameters
- Response structure và status codes
- Exception handling
- Request validation
