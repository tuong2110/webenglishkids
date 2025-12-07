<?php
/**
 * Script cập nhật thời gian tạo từ vựng thành hôm nay (cùng giờ)
 */
require_once(__DIR__ . "/configs/config.php");
require_once(__DIR__ . "/configs/function.php");

// Lấy thời gian hiện tại
$thoiGianHomNay = date('Y-m-d H:i:s');

// Cập nhật tất cả từ vựng
$result = $Database->query("UPDATE tuvung SET ThoiGianTaoTuVung = '$thoiGianHomNay' WHERE TrangThaiTuVung = 1");

// Đếm số từ vựng đã cập nhật
$soTuVung = $Database->num_rows("SELECT * FROM tuvung WHERE TrangThaiTuVung = 1");

?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cập Nhật Thời Gian Từ Vựng</title>
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
            max-width: 800px;
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
        .success-box {
            background: #d4edda;
            border-left: 4px solid #28a745;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 4px;
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
        code {
            background: #f5f5f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>✅ Cập Nhật Thời Gian Từ Vựng</h1>
        
        <?php if ($result): ?>
            <div class="success-box">
                <h3 style="color: #155724; margin-bottom: 10px;">✅ Thành công!</h3>
                <p style="color: #155724; line-height: 1.8;">
                    Đã cập nhật thời gian tạo cho <strong><?= number_format($soTuVung) ?></strong> từ vựng thành hôm nay!<br>
                    <strong>Thời gian mới:</strong> <code><?= $thoiGianHomNay ?></code>
                </p>
            </div>
        <?php else: ?>
            <div style="background: #f8d7da; border-left: 4px solid #dc3545; padding: 20px; margin-bottom: 20px; border-radius: 4px;">
                <h3 style="color: #721c24; margin-bottom: 10px;">❌ Lỗi!</h3>
                <p style="color: #721c24;">Không thể cập nhật thời gian từ vựng.</p>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <strong>Thông tin:</strong><br>
            - Tổng số từ vựng đã cập nhật: <strong><?= number_format($soTuVung) ?></strong><br>
            - Thời gian mới: <code><?= $thoiGianHomNay ?></code><br>
            - Chỉ cập nhật từ vựng có TrangThaiTuVung = 1 (đang hoạt động)
        </div>

        <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
            <h3 style="color: #856404; margin-bottom: 10px;">💡 Lưu ý:</h3>
            <p style="color: #856404; line-height: 1.8;">
                Nếu muốn cập nhật với giờ random (mỗi từ vựng một giờ khác nhau trong ngày),<br>
                hãy chạy file SQL: <code>cap_nhat_thoi_gian_tu_vung.sql</code> trong phpMyAdmin
            </p>
        </div>
    </div>
</body>
</html>

