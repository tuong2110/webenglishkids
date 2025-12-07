<?php
/**
 * Script tự động tạo 3 chặng và gán bài học vào chặng
 * Chạy script này một lần để thiết lập hệ thống chặng
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
                <a href='?confirm=yes' style='color: blue;'>http://localhost:8000/tao_3_chang.php?confirm=yes</a>
            </li>
        </ol>
        ");
    }
}

$message = '';
$error = '';

// Xử lý tạo chặng
if (isset($_GET['action']) && $_GET['action'] === 'create') {
    try {
        echo "<h2>Đang tạo 3 chặng...</h2>";
        echo "<pre>";
        
        // Lấy khóa học tiếng Anh (MaKhoaHoc = 1)
        $khoaHoc = $Database->get_row("SELECT * FROM khoahoc WHERE MaKhoaHoc = 1 AND TrangThaiKhoaHoc = 1");
        
        if (!$khoaHoc) {
            throw new Exception("Không tìm thấy khóa học tiếng Anh (MaKhoaHoc = 1)");
        }
        
        $maKhoaHoc = $khoaHoc['MaKhoaHoc'];
        
        // Kiểm tra xem đã có chặng chưa
        $existingChang = $Database->get_list("SELECT * FROM chang WHERE MaKhoaHoc = $maKhoaHoc");
        if (!empty($existingChang)) {
            echo "⚠️ Đã có chặng tồn tại. Bạn có muốn xóa và tạo lại không?\n";
            echo "Truy cập: ?confirm=yes&action=create&force=yes để xóa và tạo lại.\n";
            exit;
        }
        
        // Tạo 3 chặng
        $changs = [
            [
                'TenChang' => 'Chặng 1: Khởi Đầu',
                'MoTaChang' => 'Làm quen với từ vựng cơ bản',
                'ThuTuChang' => 1,
                'HinhAnhChang' => 'https://i.imgur.com/Ldhl3hK.png'
            ],
            [
                'TenChang' => 'Chặng 2: Phát Triển',
                'MoTaChang' => 'Mở rộng vốn từ vựng',
                'ThuTuChang' => 2,
                'HinhAnhChang' => 'https://i.imgur.com/Ldhl3hK.png'
            ],
            [
                'TenChang' => 'Chặng 3: Nâng Cao',
                'MoTaChang' => 'Thành thạo từ vựng nâng cao',
                'ThuTuChang' => 3,
                'HinhAnhChang' => 'https://i.imgur.com/Ldhl3hK.png'
            ]
        ];
        
        $maChangList = [];
        foreach ($changs as $chang) {
            $result = $Database->insert("chang", [
                'MaKhoaHoc' => $maKhoaHoc,
                'TenChang' => $chang['TenChang'],
                'MoTaChang' => $chang['MoTaChang'],
                'ThuTuChang' => $chang['ThuTuChang'],
                'HinhAnhChang' => $chang['HinhAnhChang'],
                'TrangThaiChang' => 1
            ]);
            
            if ($result) {
                // Lấy MaChang vừa tạo
                $maChang = $Database->get_row("SELECT MAX(MaChang) as MaxMaChang FROM chang WHERE MaKhoaHoc = $maKhoaHoc")["MaxMaChang"];
                $maChangList[] = $maChang;
                echo "✅ Đã tạo chặng: {$chang['TenChang']} (MaChang: $maChang)\n";
            } else {
                throw new Exception("Lỗi khi tạo chặng: {$chang['TenChang']}");
            }
        }
        
        // Lấy danh sách bài học và phân chia vào 3 chặng
        $danhSachBaiHoc = $Database->get_list("SELECT * FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc AND TrangThaiBaiHoc = 1 ORDER BY MaBaiHoc ASC");
        $tongBaiHoc = count($danhSachBaiHoc);
        
        if ($tongBaiHoc == 0) {
            echo "\n⚠️ Không có bài học nào để gán vào chặng.\n";
        } else {
            echo "\n📚 Đang gán bài học vào các chặng...\n";
            echo "Tổng số bài học: $tongBaiHoc\n\n";
            
            // Chia đều bài học vào 3 chặng
            $soBaiHocMoiChang = ceil($tongBaiHoc / 3);
            
            $changIndex = 0;
            $baiHocTrongChang = 0;
            
            foreach ($danhSachBaiHoc as $index => $baihoc) {
                $maChang = $maChangList[$changIndex];
                
                $result = $Database->update("baihoc", [
                    'MaChang' => $maChang
                ], "MaBaiHoc = " . $baihoc['MaBaiHoc'] . " AND MaKhoaHoc = " . $baihoc['MaKhoaHoc']);
                
                if ($result) {
                    echo "  ✓ Gán bài học '{$baihoc['TenBaiHoc']}' vào chặng " . ($changIndex + 1) . "\n";
                    $baiHocTrongChang++;
                    
                    // Chuyển sang chặng tiếp theo nếu đã đủ số bài học
                    if ($baiHocTrongChang >= $soBaiHocMoiChang && $changIndex < 2) {
                        $changIndex++;
                        $baiHocTrongChang = 0;
                    }
                } else {
                    echo "  ✗ Lỗi khi gán bài học '{$baihoc['TenBaiHoc']}'\n";
                }
            }
        }
        
        echo "\n========================================\n";
        echo "✅ HOÀN TẤT!\n";
        echo "========================================\n";
        echo "Đã tạo 3 chặng và gán bài học thành công!\n";
        echo "\nBây giờ bạn có thể:\n";
        echo "1. Truy cập trang khóa học để xem 3 chặng\n";
        echo "2. Click vào mỗi chặng để xem danh sách bài học\n";
        echo "3. Hoàn thành chặng 1 để mở khóa chặng 2\n";
        
        echo "</pre>";
        echo "<p><a href='" . BASE_URL("Page/KhoaHoc/1") . "'>→ Xem khóa học</a> | ";
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
    <title>Tạo 3 Chặng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
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
        <h1>🎯 Tạo 3 Chặng</h1>
        
        <?php if ($error): ?>
            <div class="error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📋 Thông tin:</h3>
            <p>Script này sẽ:</p>
            <ul>
                <li>Tạo 3 chặng cho khóa học tiếng Anh (MaKhoaHoc = 1)</li>
                <li>Tự động chia đều bài học vào 3 chặng</li>
                <li>Thiết lập hệ thống mở khóa chặng (hoàn thành chặng 1 mới mở chặng 2)</li>
            </ul>
            <p><strong>Lưu ý:</strong> Chỉ chạy script này một lần!</p>
        </div>
        
        <a href="?confirm=yes&action=create" class="btn" onclick="return confirm('Bạn có chắc chắn muốn tạo 3 chặng?');">
            🎯 Tạo 3 Chặng
        </a>
        
        <p style="margin-top: 30px;">
            <a href="index.php">← Quay về trang chủ</a>
        </p>
    </div>
</body>
</html>

