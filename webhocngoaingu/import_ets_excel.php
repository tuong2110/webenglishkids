<?php
/**
 * Script import từ vựng từ file Excel ETS
 * Tự động đọc từng sheet (mỗi sheet = 1 test)
 * Tự động tạo bài học từ Part trong cột "Phân loại"
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
                <a href='?confirm=yes' style='color: blue;'>http://localhost:8000/import_ets_excel.php?confirm=yes</a>
            </li>
        </ol>
        ");
    }
}

$message = '';
$error = '';

// Xử lý upload và import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excel_file'])) {
    if ($_FILES['excel_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['excel_file']['tmp_name'];
        $maKhoaHoc = isset($_POST['maKhoaHoc']) ? intval($_POST['maKhoaHoc']) : 0;
        
        if (empty($maKhoaHoc)) {
            $error = "Vui lòng chọn khóa học!";
        } else {
            // Kiểm tra xem có thư viện PhpSpreadsheet không
            if (!class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
                $error = "❌ Chưa cài đặt thư viện PhpSpreadsheet!<br>";
                $error .= "📦 Vui lòng chạy lệnh: <code>composer require phpoffice/phpspreadsheet</code><br>";
                $error .= "Hoặc export file Excel sang CSV và dùng script import CSV.";
            } else {
                try {
                    require_once(__DIR__ . '/vendor/autoload.php');
                    
                    use PhpOffice\PhpSpreadsheet\Spreadsheet;
                    use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
                    
                    $reader = new Xlsx();
                    $spreadsheet = $reader->load($file);
                    
                    $imported = 0;
                    $skipped = 0;
                    $errors = [];
                    $createdBaiHoc = [];
                    $partToBaiHoc = [];
                    
                    // Đọc từng sheet (mỗi sheet = 1 test)
                    $sheetNames = $spreadsheet->getSheetNames();
                    
                    foreach ($sheetNames as $sheetIndex => $sheetName) {
                        $sheet = $spreadsheet->getSheet($sheetIndex);
                        $testNumber = $sheetIndex + 1; // Test 1, 2, 3, ...
                        
                        // Đọc header (dòng 1)
                        $headerRow = $sheet->getRowIterator(1, 1)->current();
                        $headers = [];
                        foreach ($headerRow->getCellIterator() as $cell) {
                            $headers[] = $cell->getValue();
                        }
                        
                        // Tìm index các cột
                        $idxPhanLoai = array_search('Phân loại', $headers);
                        $idxTuTiengAnh = array_search('Từ tiếng Anh', $headers);
                        $idxNghiaTiengViet = array_search('Nghĩa tiếng Việt', $headers);
                        $idxAnhMy = array_search('Anh - Mỹ', $headers);
                        $idxCauViDu = array_search('Câu ví dụ', $headers);
                        
                        if ($idxPhanLoai === false || $idxTuTiengAnh === false || $idxNghiaTiengViet === false) {
                            $errors[] = "Sheet '$sheetName': Thiếu cột bắt buộc (Phân loại, Từ tiếng Anh, Nghĩa tiếng Việt)";
                            continue;
                        }
                        
                        // Đọc từng dòng (bắt đầu từ dòng 2)
                        $maTuVungCounterByBaiHoc = [];
                        $rowNumber = 0;
                        
                        foreach ($sheet->getRowIterator(2) as $row) {
                            $rowNumber++;
                            
                            try {
                                $cellIterator = $row->getCellIterator();
                                $cellIterator->setIterateOnlyExistingCells(false);
                                
                                $rowData = [];
                                foreach ($cellIterator as $cell) {
                                    $rowData[] = $cell->getValue();
                                }
                                
                                // Lấy giá trị từ các cột
                                $phanLoai = isset($rowData[$idxPhanLoai]) ? trim($rowData[$idxPhanLoai]) : '';
                                $tuTiengAnh = isset($rowData[$idxTuTiengAnh]) ? trim($rowData[$idxTuTiengAnh]) : '';
                                $nghiaTiengViet = isset($rowData[$idxNghiaTiengViet]) ? trim($rowData[$idxNghiaTiengViet]) : '';
                                $anhMy = isset($rowData[$idxAnhMy]) ? trim($rowData[$idxAnhMy]) : '';
                                $cauViDu = isset($rowData[$idxCauViDu]) ? trim($rowData[$idxCauViDu]) : '';
                                
                                // Bỏ qua dòng trống
                                if (empty($tuTiengAnh) || empty($nghiaTiengViet)) {
                                    continue;
                                }
                                
                                // Xử lý Part từ "Phân loại" (có thể là "Parrt 1", "Part 1", "Part 2", etc.)
                                $partNumber = 1; // Mặc định
                                if (!empty($phanLoai)) {
                                    // Tìm số trong "Parrt 1", "Part 1", "Part 2", etc.
                                    if (preg_match('/part\s*(\d+)/i', $phanLoai, $matches)) {
                                        $partNumber = intval($matches[1]);
                                    }
                                }
                                
                                // Tạo key cho bài học: "Part X - Test Y" (MỖI Part+Test = 1 bài học riêng)
                                // Đảm bảo mỗi Part+Test tạo 1 bài học riêng để chia nhỏ từ vựng
                                $baiHocKey = "Part $partNumber - Test $testNumber";
                                
                                // Kiểm tra xem đã tạo bài học cho part này chưa
                                if (!isset($partToBaiHoc[$baiHocKey])) {
                                    // Tìm bài học đã tồn tại
                                    $existingBaiHoc = $Database->get_row("SELECT * FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc AND TenBaiHoc = '" . $Database->escape_string($baiHocKey) . "'");
                                    
                                    if ($existingBaiHoc) {
                                        $partToBaiHoc[$baiHocKey] = $existingBaiHoc['MaBaiHoc'];
                                    } else {
                                        // Tạo bài học mới
                                        $maxBaiHoc = $Database->get_row("SELECT MAX(MaBaiHoc) as MaxBaiHoc FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc");
                                        $newMaBaiHoc = ($maxBaiHoc && $maxBaiHoc['MaxBaiHoc']) ? intval($maxBaiHoc['MaxBaiHoc']) + 1 : 1;
                                        
                                        $resultBaiHoc = $Database->insert("baihoc", [
                                            'MaBaiHoc' => $newMaBaiHoc,
                                            'MaKhoaHoc' => $maKhoaHoc,
                                            'TenBaiHoc' => $baiHocKey,
                                            'TrangThaiBaiHoc' => 1
                                        ]);
                                        
                                        if ($resultBaiHoc) {
                                            $partToBaiHoc[$baiHocKey] = $newMaBaiHoc;
                                            $createdBaiHoc[] = $baiHocKey;
                                        } else {
                                            $skipped++;
                                            $errors[] = "Sheet '$sheetName', Dòng $rowNumber: Không thể tạo bài học '$baiHocKey'";
                                            continue;
                                        }
                                    }
                                }
                                
                                $currentMaBaiHoc = $partToBaiHoc[$baiHocKey];
                                
                                // Tạo MaTuVung (reset counter cho mỗi bài học)
                                if (!isset($maTuVungCounterByBaiHoc[$currentMaBaiHoc])) {
                                    $maTuVungCounterByBaiHoc[$currentMaBaiHoc] = 1;
                                }
                                $maTuVung = $maTuVungCounterByBaiHoc[$currentMaBaiHoc]++;
                                
                                // Kiểm tra từ vựng đã tồn tại chưa
                                $checkExist = $Database->get_row("SELECT * FROM tuvung WHERE MaTuVung = $maTuVung AND MaBaiHoc = $currentMaBaiHoc AND MaKhoaHoc = $maKhoaHoc");
                                if ($checkExist) {
                                    $skipped++;
                                    continue;
                                }
                                
                                // Tạo hình ảnh placeholder (có thể cải thiện sau)
                                $hinhAnh = 'https://via.placeholder.com/300x200?text=' . urlencode($tuTiengAnh);
                                
                                // Insert từ vựng
                                $result = $Database->insert("tuvung", [
                                    'MaTuVung' => $maTuVung,
                                    'MaBaiHoc' => $currentMaBaiHoc,
                                    'MaKhoaHoc' => $maKhoaHoc,
                                    'NoiDungTuVung' => $tuTiengAnh,
                                    'DichNghia' => $nghiaTiengViet,
                                    'HinhAnh' => $hinhAnh,
                                    'AmThanh' => !empty($anhMy) ? $anhMy : null,
                                    'Diem' => 10,
                                    'TrangThaiTuVung' => 1
                                ]);
                                
                                if ($result) {
                                    $imported++;
                                    
                                    // Nếu có câu ví dụ, thêm vào bảng vidu
                                    if (!empty($cauViDu)) {
                                        $maViDu = 1; // Có thể cải thiện sau
                                        $Database->insert("vidu", [
                                            'MaViDu' => $maViDu,
                                            'MaTuVung' => $maTuVung,
                                            'MaBaiHoc' => $currentMaBaiHoc,
                                            'MaKhoaHoc' => $maKhoaHoc,
                                            'CauViDu' => $cauViDu,
                                            'DichNghia' => $nghiaTiengViet, // Có thể cải thiện
                                            'TrangThaiViDu' => 1
                                        ]);
                                    }
                                } else {
                                    $skipped++;
                                    $errors[] = "Sheet '$sheetName', Dòng $rowNumber: Lỗi khi insert từ vựng";
                                }
                                
                            } catch (Exception $e) {
                                $skipped++;
                                $errors[] = "Sheet '$sheetName', Dòng $rowNumber: " . $e->getMessage();
                            }
                        }
                    }
                    
                    $message = "✅ Import thành công!<br>";
                    $message .= "- Đã import: <strong>$imported</strong> từ vựng<br>";
                    $message .= "- Đã bỏ qua: <strong>$skipped</strong> dòng<br>";
                    
                    if (!empty($createdBaiHoc)) {
                        $message .= "- Đã tạo <strong>" . count($createdBaiHoc) . "</strong> bài học mới:<br>";
                        $message .= "<ul>";
                        foreach ($createdBaiHoc as $baiHocName) {
                            $message .= "<li>" . htmlspecialchars($baiHocName) . "</li>";
                        }
                        $message .= "</ul>";
                    }
                    
                    if (!empty($errors)) {
                        $message .= "<br><strong>Chi tiết lỗi:</strong><br>";
                        $message .= "<pre style='max-height: 300px; overflow-y: auto;'>" . implode("\n", array_slice($errors, 0, 20)) . "</pre>";
                        if (count($errors) > 20) {
                            $message .= "<p>... và " . (count($errors) - 20) . " lỗi khác</p>";
                        }
                    }
                    
                } catch (Exception $e) {
                    $error = "Lỗi: " . $e->getMessage();
                }
            }
        }
    } else {
        $error = "Lỗi upload file: " . $_FILES['excel_file']['error'];
    }
}

// Lấy danh sách khóa học
$khoaHocList = $Database->get_list("SELECT * FROM khoahoc WHERE TrangThaiKhoaHoc = 1 ORDER BY MaKhoaHoc");
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import ETS Excel</title>
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
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        select, input[type="file"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
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
        }
        .btn:hover {
            background: #45a049;
        }
        .message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        a {
            color: #4CAF50;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📥 Import ETS Excel</h1>
        
        <?php if ($message): ?>
            <div class="message success">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="message error">
                <?= $error ?>
            </div>
        <?php endif; ?>
        
        <div class="info">
            <h3>📋 Thông tin file ETS:</h3>
            <ul>
                <li><strong>File:</strong> ETS 2024 - LISTENING.xlsx</li>
                <li><strong>Số sheet:</strong> 10 (TEST 1 đến TEST 10)</li>
                <li><strong>Cấu trúc:</strong>
                    <ul>
                        <li>Mỗi sheet = 1 Test</li>
                        <li>Cột "Phân loại" = Part (ví dụ: "Parrt 1" = Part 1)</li>
                        <li>Cột "Từ tiếng Anh" = Từ vựng</li>
                        <li>Cột "Nghĩa tiếng Việt" = Nghĩa</li>
                        <li>Cột "Anh - Mỹ" = Phát âm (tùy chọn)</li>
                        <li>Cột "Câu ví dụ" = Ví dụ (tùy chọn)</li>
                    </ul>
                </li>
                <li><strong>Tự động:</strong>
                    <ul>
                        <li>Mỗi sheet (Test) sẽ được import</li>
                        <li>Mỗi Part trong "Phân loại" = 1 bài học</li>
                        <li>Tên bài học: "Test X - Part Y"</li>
                    </ul>
                </li>
            </ul>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="maKhoaHoc">Khóa học *</label>
                <select name="maKhoaHoc" id="maKhoaHoc" required>
                    <option value="">-- Chọn khóa học --</option>
                    <?php foreach ($khoaHocList as $kh): ?>
                        <option value="<?= $kh['MaKhoaHoc'] ?>" <?= (isset($_POST['maKhoaHoc']) && $_POST['maKhoaHoc'] == $kh['MaKhoaHoc']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kh['TenKhoaHoc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="excel_file">File Excel (.xlsx) *</label>
                <input type="file" name="excel_file" id="excel_file" accept=".xlsx,.xls" required>
                <small style="color: #666;">Hoặc upload file "ETS 2024 - LISTENING.xlsx"</small>
            </div>
            
            <button type="submit" class="btn">📥 Import Excel</button>
        </form>
        
        <p style="margin-top: 30px;">
            <a href="import_tuvung_csv.php?confirm=yes">→ Import từ CSV</a> | 
            <a href="read_ets.php?confirm=yes">→ Đọc file ETS</a> | 
            <a href="index.php">← Quay về trang chủ</a>
        </p>
    </div>
</body>
</html>

