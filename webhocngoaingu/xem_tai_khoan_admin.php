<?php
/**
 * Script xem thông tin tài khoản admin
 */
require_once(__DIR__ . "/configs/config.php");
require_once(__DIR__ . "/configs/function.php");

// Lấy tất cả tài khoản admin (MaQuyenHan = 2)
$listAdmin = $Database->get_list("SELECT * FROM nguoidung WHERE MaQuyenHan = 2 ORDER BY NgayDangKy DESC");

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Danh Sách Tài Khoản Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
            font-size: 28px;
        }
        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .info-box strong {
            color: #1976D2;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #4CAF50;
            color: white;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-admin {
            background: #f44336;
            color: white;
        }
        .badge-active {
            background: #4CAF50;
            color: white;
        }
        .badge-inactive {
            background: #999;
            color: white;
        }
        .password-hash {
            font-family: monospace;
            font-size: 11px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📋 Danh Sách Tài Khoản Admin</h1>
        
        <div class="info-box">
            <strong>Thông tin:</strong><br>
            - Tổng số tài khoản admin: <strong><?= count($listAdmin) ?></strong><br>
            - Tài khoản admin có quyền truy cập trang quản trị (MaQuyenHan = 2)<br>
            - Mật khẩu được mã hóa MD5 trong database
        </div>

        <?php if (empty($listAdmin)): ?>
            <p style="color: #f44336; padding: 20px; text-align: center;">
                ⚠️ Không có tài khoản admin nào trong hệ thống!
            </p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tài Khoản</th>
                        <th>Tên Hiển Thị</th>
                        <th>Email</th>
                        <th>Mật Khẩu (MD5)</th>
                        <th>Trạng Thái</th>
                        <th>Ngày Đăng Ký</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($listAdmin as $index => $admin): ?>
                        <tr>
                            <td><?= $index + 1 ?></td>
                            <td><strong><?= htmlspecialchars($admin['TaiKhoan']) ?></strong></td>
                            <td><?= htmlspecialchars($admin['TenHienThi']) ?></td>
                            <td><?= htmlspecialchars($admin['Email'] ?? 'N/A') ?></td>
                            <td class="password-hash"><?= htmlspecialchars($admin['MatKhau']) ?></td>
                            <td>
                                <?php if ($admin['TrangThai'] == 1): ?>
                                    <span class="badge badge-active">Hoạt động</span>
                                <?php else: ?>
                                    <span class="badge badge-inactive">Khóa</span>
                                <?php endif; ?>
                                <span class="badge badge-admin">Admin</span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($admin['NgayDangKy'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
            <h3 style="color: #856404; margin-bottom: 10px;">🔐 Tài Khoản Admin Mặc Định:</h3>
            <p style="color: #856404; line-height: 1.8;">
                <strong>Tài khoản:</strong> <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">admin</code><br>
                <strong>Mật khẩu:</strong> <code style="background: #fff; padding: 2px 6px; border-radius: 3px;">admin</code><br>
                <strong>MD5 Hash:</strong> <code style="background: #fff; padding: 2px 6px; border-radius: 3px; font-size: 11px;">21232f297a57a5a743894a0e4a801fc3</code><br>
                <br>
                <strong>URL Admin:</strong><br>
                - Dashboard: <a href="<?= BASE_URL('admin/home') ?>" target="_blank"><?= BASE_URL('admin/home') ?></a><br>
                - Quản lý người dùng: <a href="<?= BASE_URL('admin/users') ?>" target="_blank"><?= BASE_URL('admin/users') ?></a><br>
                - Quản lý khóa học: <a href="<?= BASE_URL('admin/courses') ?>" target="_blank"><?= BASE_URL('admin/courses') ?></a><br>
                - Cài đặt hệ thống: <a href="<?= BASE_URL('admin/system') ?>" target="_blank"><?= BASE_URL('admin/system') ?></a><br>
                - ChatGPT: <a href="<?= BASE_URL('admin/chatgpt') ?>" target="_blank"><?= BASE_URL('admin/chatgpt') ?></a>
            </p>
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 8px; text-align: center;">
            <p style="color: #666; font-size: 14px;">
                💡 <strong>Lưu ý:</strong> Để truy cập admin, bạn cần đăng nhập với tài khoản có MaQuyenHan = 2
            </p>
        </div>
    </div>
</body>
</html>

