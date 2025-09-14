# Social Media Manager

Website app quản lý nhiều fanpage Facebook và Zalo OA tập trung được xây dựng bằng PHP.

## Tính năng chính

### 🔐 Hệ thống đăng nhập
- Đăng ký/Đăng nhập tài khoản
- Quản lý thông tin cá nhân
- Phân quyền người dùng

### 📱 Quản lý Fanpage Facebook
- Kết nối nhiều fanpage Facebook
- Đồng bộ thông tin fanpage
- Quản lý quyền truy cập

### 💬 Quản lý Zalo OA
- Kết nối nhiều Zalo Official Account
- Đồng bộ thông tin Zalo OA
- Quản lý quyền truy cập

### 📝 Quản lý bài đăng
- Tạo và chỉnh sửa bài đăng
- Đăng bài lên Facebook và Zalo
- Upload hình ảnh
- Lưu bản nháp

### ⏰ Lên lịch đăng bài
- Lên lịch đăng bài tự động
- Hỗ trợ nhiều loại lịch:
  - Một lần
  - Hàng ngày
  - Hàng tuần
  - Hàng tháng
- Quản lý lịch đăng bài

### 📊 Thống kê và báo cáo
- Dashboard tổng quan
- Thống kê bài đăng
- Thống kê tương tác
- Biểu đồ hoạt động
- Top fanpage/Zalo OA

### ⚙️ Cài đặt
- Cài đặt thông tin cá nhân
- Cài đặt API
- Quản lý tài khoản

## Công nghệ sử dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript, Bootstrap 5
- **Icons**: Font Awesome 6
- **Charts**: Chart.js
- **Tables**: DataTables
- **Select**: Select2

## Cài đặt

### Yêu cầu hệ thống
- PHP 7.4 hoặc cao hơn
- MySQL 5.7 hoặc cao hơn
- Web server (Apache/Nginx)
- Extension PHP: PDO, cURL, JSON

### Hướng dẫn cài đặt

1. **Clone repository**
```bash
git clone <repository-url>
cd social-media-manager
```

2. **Cấu hình database**
- Tạo database mới
- Import file `database/schema.sql`
- Cập nhật thông tin database trong `config/database.php`

3. **Cấu hình web server**
- Trỏ document root đến thư mục dự án
- Đảm bảo thư mục `uploads/` có quyền ghi

4. **Cấu hình API**
- Đăng ký Facebook App tại [Facebook Developers](https://developers.facebook.com/)
- Đăng ký Zalo App tại [Zalo Developers](https://developers.zalo.me/)
- Cập nhật thông tin API trong database

5. **Truy cập ứng dụng**
- Mở trình duyệt và truy cập URL của website
- Đăng ký tài khoản admin đầu tiên

## Cấu trúc thư mục

```
social-media-manager/
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── script.js
├── config/
│   └── database.php
├── database/
│   └── schema.sql
├── includes/
│   └── functions.php
├── templates/
│   ├── header.php
│   └── footer.php
├── uploads/
├── analytics.php
├── fanpages.php
├── index.php
├── login.php
├── logout.php
├── posts.php
├── register.php
├── schedule.php
├── settings.php
├── zalo.php
└── README.md
```

## API Integration

### Facebook API
- Sử dụng Facebook Graph API
- Quyền cần thiết: `pages_manage_posts`, `pages_read_engagement`
- Lấy Access Token từ [Graph API Explorer](https://developers.facebook.com/tools/explorer/)

### Zalo API
- Sử dụng Zalo Open API
- Quyền cần thiết: `oa.info`, `oa.message`
- Lấy Access Token từ [Zalo Developers](https://developers.zalo.me/)

## Bảo mật

- Mã hóa mật khẩu bằng `password_hash()`
- XSS protection với `htmlspecialchars()`
- SQL injection protection với prepared statements
- CSRF protection (có thể thêm token)
- Validation input data

## Phát triển

### Thêm tính năng mới
1. Tạo file PHP mới trong thư mục gốc
2. Thêm route trong sidebar
3. Cập nhật database schema nếu cần
4. Thêm JavaScript xử lý nếu cần

### Customization
- Chỉnh sửa `assets/css/style.css` để thay đổi giao diện
- Chỉnh sửa `templates/header.php` và `templates/footer.php` để thay đổi layout
- Thêm functions mới trong `includes/functions.php`

## Troubleshooting

### Lỗi kết nối database
- Kiểm tra thông tin database trong `config/database.php`
- Đảm bảo MySQL service đang chạy
- Kiểm tra quyền truy cập database

### Lỗi API
- Kiểm tra Access Token có hợp lệ không
- Kiểm tra quyền API có đủ không
- Kiểm tra kết nối internet

### Lỗi upload file
- Kiểm tra quyền ghi thư mục `uploads/`
- Kiểm tra cấu hình `upload_max_filesize` trong PHP
- Kiểm tra cấu hình `post_max_size` trong PHP

## License

MIT License - Xem file LICENSE để biết thêm chi tiết.

## Support

Nếu gặp vấn đề, vui lòng tạo issue trên GitHub hoặc liên hệ qua email.

## Changelog

### Version 1.0.0
- Tính năng đăng nhập/đăng ký
- Quản lý fanpage Facebook
- Quản lý Zalo OA
- Quản lý bài đăng
- Lên lịch đăng bài
- Dashboard thống kê
- Giao diện responsive

## Roadmap

### Version 1.1.0
- [ ] Tích hợp Instagram
- [ ] Tích hợp TikTok
- [ ] Quản lý comment
- [ ] Auto reply
- [ ] Analytics nâng cao

### Version 1.2.0
- [ ] Mobile app
- [ ] API REST
- [ ] Webhook support
- [ ] Multi-language
- [ ] Theme customization