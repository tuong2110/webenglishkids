<?php
/**
 * Script đọc và hiển thị cấu trúc file ETS Excel
 * Giúp xem format dữ liệu trước khi import
 */

require_once(__DIR__ . "/configs/config.php");
require_once(__DIR__ . "/configs/function.php");

// Kiểm tra quyền admin hoặc cho phép chạy với tham số confirm
$allowDirectRun = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

if (!$allowDirectRun) {
    if (!isset($_SESSION["account"]) || $_SESSION["account"] !== "admin") {
        die("
        <h2>⚠️ Cần quyền admin để chạy script này!</h2>
        <p>Có 2 cách để chạy:</p>
        <ol>
            <li><strong>Đăng nhập với tài khoản admin</strong> rồi truy cập lại trang này</li>
            <li><strong>Chạy trực tiếp</strong> bằng cách thêm <code>?confirm=yes</code> vào URL:<br>
                <a href='?confirm=yes' style='color: blue;'>http://localhost:8000/read_ets.php?confirm=yes</a>
            </li>
        </ol>
        ");
    }
}

$filePath = __DIR__ . "/../ETS 2024 - LISTENING.xlsx";
$content = '';
$error = '';

if (!file_exists($filePath)) {
    $error = "Không tìm thấy file: ETS 2024 - LISTENING.xlsx<br>Vui lòng đảm bảo file nằm trong thư mục gốc của project.";
} else {
    // Đọc file Excel bằng cách convert sang CSV tạm thời
    // Hoặc hướng dẫn người dùng export sang CSV
    
    // Kiểm tra xem có thể đọc được không
    $fileSize = filesize($filePath);
    $content = "<h3>📄 Thông tin file:</h3>";
    $content .= "<p><strong>Tên file:</strong> ETS 2024 - LISTENING.xlsx</p>";
    $content .= "<p><strong>Kích thước:</strong> " . number_format($fileSize / 1024, 2) . " KB</p>";
    $content .= "<p><strong>Đường dẫn:</strong> " . htmlspecialchars($filePath) . "</p>";
    
    $content .= "<hr>";
    $content .= "<h3>📋 Hướng dẫn:</h3>";
    $content .= "<p>File Excel (.xlsx) cần được chuyển đổi sang CSV để import.</p>";
    $content .= "<ol>";
    $content .= "<li>Mở file <strong>ETS 2024 - LISTENING.xlsx</strong> bằng Excel</li>";
    $content .= "<li>Chọn <strong>File → Save As</strong> (hoặc <strong>Lưu dưới dạng</strong>)</li>";
    $content .= "<li>Chọn định dạng <strong>CSV (Comma delimited) (*.csv)</strong></li>";
    $content .= "<li>Lưu file với tên <strong>ETS_2024_LISTENING.csv</strong></li>";
    $content .= "<li>Sử dụng script import CSV để import dữ liệu</li>";
    $content .= "</ol>";
    
    $content .= "<hr>";
    $content .= "<h3>💡 Format CSV mong đợi:</h3>";
    $content .= "<p>Dựa trên yêu cầu của bạn (5 test, mỗi test 1 part), format CSV nên là:</p>";
    $content .= "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    $content .= "Test,Part,NoiDungTuVung,DichNghia,HinhAnh,AmThanh,Diem\n";
    $content .= "1,1,word1,meaning1,image_url1,audio_url1,10\n";
    $content .= "1,1,word2,meaning2,image_url2,audio_url2,10\n";
    $content .= "2,1,word3,meaning3,image_url3,audio_url3,10\n";
    $content .= "...\n";
    $content .= "</pre>";
    
    $content .= "<p><strong>Hoặc đơn giản hơn:</strong></p>";
    $content .= "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    $content .= "Test,Part,Word,Meaning\n";
    $content .= "1,1,hello,xin chào\n";
    $content .= "1,1,hi,chào\n";
    $content .= "2,1,good,tốt\n";
    $content .= "...\n";
    $content .= "</pre>";
    
    $content .= "<hr>";
    $content .= "<h3>🚀 Bước tiếp theo:</h3>";
    $content .= "<ol>";
    $content .= "<li>Export file Excel sang CSV (theo hướng dẫn trên)</li>";
    $content .= "<li>Truy cập: <a href='import_tuvung_csv.php?confirm=yes'>Import Từ Vựng từ CSV</a></li>";
    $content .= "<li>Bật checkbox 'Tự động tạo bài học từ Part'</li>";
    $content .= "<li>Chọn khóa học và upload file CSV</li>";
    $content .= "</ol>";
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đọc File ETS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 10px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        pre {
            background: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            overflow-x: auto;
            font-size: 13px;
            line-height: 1.5;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
            font-weight: bold;
        }
        a:hover {
            text-decoration: underline;
        }
        hr {
            border: none;
            border-top: 1px solid #ddd;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📖 Đọc File ETS</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <?php if ($content): ?>
            <?= $content ?>
        <?php endif; ?>
        
        <p style="margin-top: 30px;">
            <a href="import_tuvung_csv.php?confirm=yes">→ Chuyển đến Import CSV</a> | 
            <a href="index.php">← Quay về trang chủ</a>
        </p>
    </div>
</body>
</html>


