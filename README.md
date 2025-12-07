# Web Học Ngoại Ngữ - PHP Project

## Cấu trúc dự án

```
webhocngoaingu/
├── assets/          # CSS, JavaScript, Images
├── class/          # PHP Classes
├── configs/        # PHP Configuration files
├── public/         # PHP Templates và Pages
│   ├── admin/      # Admin pages
│   ├── client/     # Client pages
│   └── callback/  # OAuth callbacks
└── index.php       # Entry point
```

## Yêu cầu

- PHP 7.4 trở lên
- MySQL 5.7 trở lên
- Apache/Nginx web server

## Cài đặt

1. **Cấu hình database:**
   - Tạo database `hocngoaingu` trong MySQL
   - Import file `webhocngoaingu.sql`
   - Cập nhật thông tin database trong `webhocngoaingu/configs/config.php`

3. **Cấu hình web server:**
   - Đặt document root trỏ đến thư mục `webhocngoaingu`
   - Bật mod_rewrite cho Apache
   - Hoặc cấu hình Nginx rewrite rules

4. **Chạy ứng dụng:**
   - Truy cập: `http://localhost/webhocngoaingu`
   - Hoặc cấu hình virtual host

## Database

- File SQL: `webhocngoaingu.sql`
- Database name: `hocngoaingu`
- Cập nhật thông tin kết nối trong `webhocngoaingu/configs/config.php`

## Lưu ý

- Đảm bảo thư mục `webhocngoaingu/assets/uploads` có quyền ghi
- Kiểm tra cấu hình PHP (upload_max_filesize, post_max_size)
- Cấu hình email trong `webhocngoaingu/configs/config.php` nếu cần gửi email
