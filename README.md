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

## Cron Job - Email Tự Động

Để bật tính năng email nhắc nhở học tập và báo cáo tiến trình học tập, bạn cần cài đặt Cron Job cho các file sau:

### 1. Báo cáo tiến trình học tập
Gửi email báo cáo hàng ngày về các từ vựng đã học trong ngày.

**File:** `webhocngoaingu/public/cron-job/bao_cao_hoc_tap.php`

**Cài đặt Cron Job (chạy mỗi ngày lúc 20:00):**
```bash
0 20 * * * /usr/bin/php /đường/dẫn/đến/webhocngoaingu/public/cron-job/bao_cao_hoc_tap.php
```

**Hoặc sử dụng wget/curl (nếu chạy qua web server):**
```bash
0 20 * * * /usr/bin/wget -q -O - http://yourdomain.com/webhocngoaingu/public/cron-job/bao_cao_hoc_tap.php > /dev/null 2>&1
```

### 2. Nhắc nhở học tập
Gửi email nhắc nhở vào lúc 10:00 và 18:00 hàng ngày cho người dùng chưa đạt mục tiêu hoặc chưa học trong ngày.

**File:** `webhocngoaingu/public/cron-job/nhac_nho_hoc_tap.php`

**Cài đặt Cron Job (chạy mỗi giờ, script tự kiểm tra thời gian):**
```bash
0 * * * * /usr/bin/php /đường/dẫn/đến/webhocngoaingu/public/cron-job/nhac_nho_hoc_tap.php
```

**Hoặc chạy cụ thể vào 10:00 và 18:00:**
```bash
0 10,18 * * * /usr/bin/php /đường/dẫn/đến/webhocngoaingu/public/cron-job/nhac_nho_hoc_tap.php
```

**Hoặc sử dụng wget/curl:**
```bash
0 10,18 * * * /usr/bin/wget -q -O - http://yourdomain.com/webhocngoaingu/public/cron-job/nhac_nho_hoc_tap.php > /dev/null 2>&1
```

### Cách cài đặt Cron Job:

1. **Mở crontab editor:**
   ```bash
   crontab -e
   ```

2. **Thêm các dòng cron job ở trên** (thay thế đường dẫn và domain phù hợp)

3. **Lưu và thoát**

4. **Kiểm tra cron job đã được thêm:**
   ```bash
   crontab -l
   ```

### Lưu ý:
- Thay `/đường/dẫn/đến/` bằng đường dẫn thực tế đến thư mục dự án
- Thay `yourdomain.com` bằng domain thực tế của bạn
- Đảm bảo PHP có quyền thực thi và có thể kết nối database
- Người dùng cần bật tính năng trong cài đặt tài khoản:
  - `BaoCaoTienTrinhHocTap = 1` cho báo cáo tiến trình
  - `NhacNhoTienTrinhHocTap = 1` cho nhắc nhở học tập

## Lưu ý

- Đảm bảo thư mục `webhocngoaingu/assets/uploads` có quyền ghi
- Kiểm tra cấu hình PHP (upload_max_filesize, post_max_size)
- Cấu hình email trong `webhocngoaingu/configs/config.php` nếu cần gửi email

## Cập nhật

- **Lần cập nhật gần nhất:** 07 December, 2025
- **Phiên bản:** 1.0.0
- **Build Date:** 2025-12-07
