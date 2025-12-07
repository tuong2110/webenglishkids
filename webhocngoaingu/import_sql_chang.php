<?php
/**
 * Script tự động import SQL để tạo bảng chặng
 * Chạy script này để tự động tạo bảng chang và hoanthanhchang
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
                <a href='?confirm=yes' style='color: blue;'>http://localhost:8000/import_sql_chang.php?confirm=yes</a>
            </li>
        </ol>
        ");
    }
}

$message = '';
$error = '';

// Xử lý import SQL
if (isset($_GET['action']) && $_GET['action'] === 'import') {
    try {
        echo "<h2>Đang import SQL...</h2>";
        echo "<pre>";
        
        // Đọc file SQL
        $sqlFile = __DIR__ . '/tao_bang_chang.sql';
        if (!file_exists($sqlFile)) {
            throw new Exception("Không tìm thấy file: tao_bang_chang.sql");
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Tách các câu lệnh SQL (loại bỏ comment và chia theo dấu ;)
        $sql = preg_replace('/--.*$/m', '', $sql); // Loại bỏ comment
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql); // Loại bỏ comment block
        $statements = array_filter(array_map('trim', explode(';', $sql)));
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($statements as $statement) {
            if (empty($statement)) {
                continue;
            }
            
            // Bỏ qua các câu lệnh SET, START TRANSACTION, COMMIT
            if (preg_match('/^(SET|START|COMMIT|\/\*)/i', trim($statement))) {
                continue;
            }
            
            try {
                // Sử dụng Database class để thực thi
                $result = $Database->query($statement);
                if ($result) {
                    $successCount++;
                    echo "✅ " . substr($statement, 0, 50) . "...\n";
                } else {
                    $errorCount++;
                    // Lấy lỗi từ database
                    $errorMsg = $Database->get_error();
                    
                    // Bỏ qua lỗi "table already exists" hoặc "duplicate key"
                    if (strpos($errorMsg, 'already exists') === false && 
                        strpos($errorMsg, 'Duplicate') === false && 
                        strpos($errorMsg, 'Duplicate column') === false && 
                        strpos($errorMsg, 'already exist') === false &&
                        strpos($errorMsg, 'Duplicate key') === false) {
                        if (!empty($errorMsg)) {
                            echo "⚠️ " . substr($statement, 0, 50) . "...\n";
                            echo "   Lỗi: $errorMsg\n";
                        } else {
                            echo "ℹ️ " . substr($statement, 0, 50) . "... (có thể đã tồn tại)\n";
                        }
                    } else {
                        echo "ℹ️ " . substr($statement, 0, 50) . "... (đã tồn tại)\n";
                    }
                }
            } catch (Exception $e) {
                $errorCount++;
                echo "❌ Lỗi: " . $e->getMessage() . "\n";
            }
        }
        
        echo "\n========================================\n";
        echo "✅ HOÀN TẤT!\n";
        echo "========================================\n";
        echo "Thành công: $successCount câu lệnh\n";
        if ($errorCount > 0) {
            echo "Cảnh báo: $errorCount câu lệnh (có thể do đã tồn tại)\n";
        }
        echo "\nBây giờ bạn có thể tạo 3 chặng:\n";
        echo "<a href='tao_3_chang.php?confirm=yes&action=create'>Tạo 3 chặng</a>\n";
        
        echo "</pre>";
        echo "<p><a href='tao_3_chang.php?confirm=yes&action=create'>→ Tạo 3 chặng</a> | ";
        echo "<a href='index.php'>← Quay về trang chủ</a></p>";
        
        exit;
        
    } catch (Exception $e) {
        $error = "Lỗi: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import SQL - Tạo Bảng Chặng</title>
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
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .method {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 20px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .method h3 {
            color: #495057;
            margin-top: 0;
        }
        .method ol {
            margin-left: 20px;
        }
        .method code {
            background: #e9ecef;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .btn {
            background: #4CAF50;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #45a049;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        a {
            color: #007bff;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Import SQL - Tạo Bảng Chặng</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📋 Thông tin:</h3>
            <p>Script này sẽ tự động import file <code>tao_bang_chang.sql</code> để tạo:</p>
            <ul>
                <li>Bảng <code>chang</code> - Lưu thông tin các chặng</li>
                <li>Bảng <code>hoanthanhchang</code> - Theo dõi tiến độ hoàn thành chặng</li>
                <li>Thêm cột <code>MaChang</code> vào bảng <code>baihoc</code></li>
            </ul>
        </div>
        
        <div class="method">
            <h3>🚀 Cách 1: Tự động import (Khuyến nghị)</h3>
            <p>Click nút bên dưới để tự động import SQL:</p>
            <a href="?confirm=yes&action=import" class="btn" onclick="return confirm('Bạn có chắc chắn muốn import SQL?');">
                📥 Import SQL Tự Động
            </a>
        </div>
        
        <div class="method">
            <h3>📝 Cách 2: Import qua phpMyAdmin</h3>
            <ol>
                <li>Mở trình duyệt và truy cập: <code>http://localhost/phpmyadmin</code></li>
                <li>Chọn database <code>hocngoaingu</code> ở cột bên trái</li>
                <li>Click tab <strong>"SQL"</strong> ở phía trên</li>
                <li>Mở file <code>webhocngoaingu/tao_bang_chang.sql</code> bằng Notepad</li>
                <li>Copy toàn bộ nội dung và paste vào ô SQL</li>
                <li>Click nút <strong>"Go"</strong> hoặc <strong>"Thực hiện"</strong></li>
            </ol>
        </div>
        
        <div class="method">
            <h3>💻 Cách 3: Import qua MySQL Command Line</h3>
            <ol>
                <li>Mở Command Prompt hoặc PowerShell</li>
                <li>Chạy lệnh:
                    <pre style="background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto;">
cd D:\SourceCodeWebHocNgoaiNgu\webhocngoaingu
mysql -u root -p hocngoaingu < tao_bang_chang.sql
                    </pre>
                </li>
                <li>Nhập mật khẩu MySQL (nếu có, mặc định XAMPP là rỗng)</li>
            </ol>
        </div>
        
        <div class="method">
            <h3>📂 Đường dẫn file SQL:</h3>
            <p><code>D:\SourceCodeWebHocNgoaiNgu\webhocngoaingu\tao_bang_chang.sql</code></p>
        </div>
        
        <p style="margin-top: 30px;">
            <a href="tao_3_chang.php?confirm=yes" class="btn-secondary">→ Tạo 3 chặng</a> | 
            <a href="index.php">← Quay về trang chủ</a>
        </p>
    </div>
</body>
</html>

