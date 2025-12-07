<?php
/**
 * Script import từ vựng từ file CSV
 * Format CSV: MaTuVung,MaBaiHoc,MaKhoaHoc,NoiDungTuVung,DichNghia,HinhAnh,AmThanh,Diem
 * Hoặc: NoiDungTuVung,DichNghia,HinhAnh,AmThanh (tự động tạo MaTuVung)
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
                <a href='?confirm=yes' style='color: blue;'>http://localhost:8000/import_tuvung_csv.php?confirm=yes</a>
            </li>
        </ol>
        ");
    }
}

$message = '';
$error = '';

// Xử lý upload và import
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['csv_file'])) {
    if ($_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['csv_file']['tmp_name'];
        $maKhoaHoc = isset($_POST['maKhoaHoc']) ? intval($_POST['maKhoaHoc']) : 0;
        $autoCreateBaiHoc = isset($_POST['autoCreateBaiHoc']) && $_POST['autoCreateBaiHoc'] === '1';
        $maBaiHoc = isset($_POST['maBaiHoc']) ? intval($_POST['maBaiHoc']) : 0;
        $autoMaTuVung = isset($_POST['autoMaTuVung']) && $_POST['autoMaTuVung'] === '1';
        
        if (empty($maKhoaHoc)) {
            $error = "Vui lòng chọn khóa học!";
        } else if (!$autoCreateBaiHoc && empty($maBaiHoc)) {
            $error = "Vui lòng chọn bài học hoặc bật 'Tự động tạo bài học từ Part'!";
        } else {
            try {
                $handle = fopen($file, 'r');
                if ($handle === false) {
                    throw new Exception("Không thể đọc file CSV!");
                }
                
                $imported = 0;
                $skipped = 0;
                $errors = [];
                $lineNumber = 0;
                $maTuVungCounter = 1;
                $maTuVungCounterByBaiHoc = []; // Counter riêng cho mỗi bài học
                
                // Đọc header (dòng đầu tiên)
                $header = fgetcsv($handle);
                if ($header === false) {
                    throw new Exception("File CSV không hợp lệ!");
                }
                
                // Loại bỏ BOM và trim các header
                foreach ($header as $key => $value) {
                    // Loại bỏ BOM (UTF-8 BOM: EF BB BF)
                    $header[$key] = trim($value);
                    // Loại bỏ BOM nếu có
                    if (substr($header[$key], 0, 3) === "\xEF\xBB\xBF") {
                        $header[$key] = substr($header[$key], 3);
                    }
                    $header[$key] = trim($header[$key]);
                }
                
                // Xác định format CSV
                $hasMaTuVung = in_array('MaTuVung', $header) || in_array('maTuVung', $header);
                $hasMaBaiHoc = in_array('MaBaiHoc', $header) || in_array('maBaiHoc', $header);
                $hasMaKhoaHoc = in_array('MaKhoaHoc', $header) || in_array('maKhoaHoc', $header);
                
                // Tìm index của các cột (không phân biệt hoa thường)
                $headerLower = array_map('strtolower', array_map('trim', $header));
                $idxNoiDung = array_search('noidungtuvung', $headerLower);
                if ($idxNoiDung === false) {
                    $idxNoiDung = array_search('tuvung', $headerLower);
                }
                if ($idxNoiDung === false) {
                    $idxNoiDung = array_search('word', $headerLower);
                }
                
                $idxDichNghia = array_search('dichnghia', $headerLower);
                if ($idxDichNghia === false) {
                    $idxDichNghia = array_search('meaning', $headerLower);
                }
                if ($idxDichNghia === false) {
                    $idxDichNghia = array_search('nghia', $headerLower);
                }
                
                $idxHinhAnh = array_search('hinhanh', $headerLower);
                if ($idxHinhAnh === false) {
                    $idxHinhAnh = array_search('image', $headerLower);
                }
                
                $idxAmThanh = array_search('amthanh', $headerLower);
                if ($idxAmThanh === false) {
                    $idxAmThanh = array_search('audio', $headerLower);
                }
                if ($idxAmThanh === false) {
                    $idxAmThanh = array_search('sound', $headerLower);
                }
                
                $idxDiem = array_search('diem', $headerLower);
                if ($idxDiem === false) {
                    $idxDiem = array_search('point', $headerLower);
                }
                
                // Tìm cột Test và Part (tìm chính xác hoặc chứa từ khóa)
                $idxTest = false;
                $idxPart = false;
                
                // Tìm Test (ưu tiên tìm chính xác trước)
                foreach ($headerLower as $idx => $h) {
                    $h = trim($h);
                    if ($h === 'test') {
                        $idxTest = $idx;
                        break;
                    }
                }
                // Nếu không tìm thấy chính xác, tìm chứa "test"
                if ($idxTest === false) {
                    foreach ($headerLower as $idx => $h) {
                        if (stripos(trim($h), 'test') !== false) {
                            $idxTest = $idx;
                            break;
                        }
                    }
                }
                
                // Tìm Part (ưu tiên tìm chính xác trước)
                foreach ($headerLower as $idx => $h) {
                    $h = trim($h);
                    if ($h === 'part') {
                        $idxPart = $idx;
                        break;
                    }
                }
                // Nếu không tìm thấy chính xác, tìm chứa "part"
                if ($idxPart === false) {
                    foreach ($headerLower as $idx => $h) {
                        if (stripos(trim($h), 'part') !== false) {
                            $idxPart = $idx;
                            break;
                        }
                    }
                }
                
                if ($idxNoiDung === false || $idxDichNghia === false) {
                    throw new Exception("CSV phải có cột 'NoiDungTuVung' (hoặc 'TuVung', 'Word') và 'DichNghia' (hoặc 'Meaning', 'Nghia')!");
                }
                
                // Nếu tự động tạo bài học từ Part, cần có cả cột Part và Test
                if ($autoCreateBaiHoc && $idxPart === false) {
                    throw new Exception("Để tự động tạo bài học từ Part, CSV phải có cột 'Part'!");
                }
                if ($autoCreateBaiHoc && $idxTest === false) {
                    throw new Exception("Để tự động tạo bài học từ Part, CSV phải có cột 'Test' để chia nhỏ từ vựng!");
                }
                
                // Map để lưu part -> MaBaiHoc (tránh tạo trùng)
                $partToBaiHoc = [];
                $createdBaiHoc = [];
                
                // Đọc và import từng dòng
                while (($row = fgetcsv($handle)) !== false) {
                    $lineNumber++;
                    
                    // Bỏ qua dòng trống
                    if (empty(array_filter($row))) {
                        continue;
                    }
                    
                    try {
                        // Lấy giá trị từ CSV
                        $noiDungTuVung = isset($row[$idxNoiDung]) ? trim($row[$idxNoiDung]) : '';
                        $dichNghia = isset($row[$idxDichNghia]) ? trim($row[$idxDichNghia]) : '';
                        $hinhAnh = isset($row[$idxHinhAnh]) ? trim($row[$idxHinhAnh]) : '';
                        $amThanh = isset($row[$idxAmThanh]) ? trim($row[$idxAmThanh]) : '';
                        $diem = isset($row[$idxDiem]) ? intval($row[$idxDiem]) : 10;
                        
                        // Kiểm tra dữ liệu bắt buộc
                        if (empty($noiDungTuVung) || empty($dichNghia)) {
                            $skipped++;
                            $errors[] = "Dòng $lineNumber: Thiếu NoiDungTuVung hoặc DichNghia";
                            continue;
                        }
                        
                        // Xác định MaBaiHoc (tự động tạo từ Part nếu cần)
                        $currentMaBaiHoc = $maBaiHoc;
                        if ($autoCreateBaiHoc && $idxPart !== false) {
                            $partValue = isset($row[$idxPart]) ? trim($row[$idxPart]) : '';
                            $testValue = ($idxTest !== false && isset($row[$idxTest])) ? trim($row[$idxTest]) : '';
                            
                            if (empty($partValue)) {
                                $skipped++;
                                $errors[] = "Dòng $lineNumber: Thiếu giá trị Part";
                                continue;
                            }
                            
                            // Bắt buộc phải có Test để chia nhỏ từ vựng
                            if (empty($testValue)) {
                                $skipped++;
                                $errors[] = "Dòng $lineNumber: Thiếu giá trị Test. Cần có cả Test và Part để chia nhỏ từ vựng!";
                                continue;
                            }
                            
                            // Tạo key duy nhất cho bài học: "Part X - Test Y" (MỖI Part+Test = 1 bài học riêng)
                            $partKey = "Part $partValue - Test $testValue";
                            
                            // Kiểm tra xem đã tạo bài học cho part này chưa
                            if (!isset($partToBaiHoc[$partKey])) {
                                // Tìm bài học đã tồn tại với tên tương tự
                                $existingBaiHoc = $Database->get_row("SELECT * FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc AND TenBaiHoc = '" . $Database->escape_string($partKey) . "'");
                                
                                if ($existingBaiHoc) {
                                    $partToBaiHoc[$partKey] = $existingBaiHoc['MaBaiHoc'];
                                } else {
                                    // Tạo bài học mới
                                    // Tìm MaBaiHoc tiếp theo (lấy max + 1)
                                    $maxBaiHoc = $Database->get_row("SELECT MAX(MaBaiHoc) as MaxBaiHoc FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc");
                                    $newMaBaiHoc = ($maxBaiHoc && $maxBaiHoc['MaxBaiHoc']) ? intval($maxBaiHoc['MaxBaiHoc']) + 1 : 1;
                                    
                                    // Insert bài học mới
                                    $resultBaiHoc = $Database->insert("baihoc", [
                                        'MaBaiHoc' => $newMaBaiHoc,
                                        'MaKhoaHoc' => $maKhoaHoc,
                                        'TenBaiHoc' => $partKey,
                                        'TrangThaiBaiHoc' => 1
                                    ]);
                                    
                                    if ($resultBaiHoc) {
                                        $partToBaiHoc[$partKey] = $newMaBaiHoc;
                                        $createdBaiHoc[] = $partKey;
                                    } else {
                                        $skipped++;
                                        $errors[] = "Dòng $lineNumber: Không thể tạo bài học '$partKey'";
                                        continue;
                                    }
                                }
                            }
                            
                            $currentMaBaiHoc = $partToBaiHoc[$partKey];
                        }
                        
                        // Xác định MaTuVung
                        if ($autoMaTuVung) {
                            // Tự động tạo MaTuVung (reset counter cho mỗi bài học)
                            if (!isset($maTuVungCounterByBaiHoc[$currentMaBaiHoc])) {
                                $maTuVungCounterByBaiHoc[$currentMaBaiHoc] = 1;
                            }
                            $maTuVung = $maTuVungCounterByBaiHoc[$currentMaBaiHoc]++;
                        } else {
                            // Lấy từ CSV hoặc dùng counter
                            if ($hasMaTuVung) {
                                $idxMaTuVung = array_search('MaTuVung', $header);
                                if ($idxMaTuVung === false) {
                                    $idxMaTuVung = array_search('maTuVung', $header);
                                }
                                $maTuVung = isset($row[$idxMaTuVung]) ? intval($row[$idxMaTuVung]) : $maTuVungCounter++;
                            } else {
                                if (!isset($maTuVungCounterByBaiHoc[$currentMaBaiHoc])) {
                                    $maTuVungCounterByBaiHoc[$currentMaBaiHoc] = 1;
                                }
                                $maTuVung = $maTuVungCounterByBaiHoc[$currentMaBaiHoc]++;
                            }
                        }
                        
                        // Kiểm tra từ vựng đã tồn tại chưa
                        $checkExist = $Database->get_row("SELECT * FROM tuvung WHERE MaTuVung = $maTuVung AND MaBaiHoc = $currentMaBaiHoc AND MaKhoaHoc = $maKhoaHoc");
                        if ($checkExist) {
                            $skipped++;
                            $errors[] = "Dòng $lineNumber: Từ vựng với MaTuVung=$maTuVung đã tồn tại trong bài học $currentMaBaiHoc";
                            continue;
                        }
                        
                        // Insert vào database
                        $result = $Database->insert("tuvung", [
                            'MaTuVung' => $maTuVung,
                            'MaBaiHoc' => $currentMaBaiHoc,
                            'MaKhoaHoc' => $maKhoaHoc,
                            'NoiDungTuVung' => $noiDungTuVung,
                            'DichNghia' => $dichNghia,
                            'HinhAnh' => !empty($hinhAnh) ? $hinhAnh : 'https://via.placeholder.com/300x200?text=No+Image',
                            'AmThanh' => !empty($amThanh) ? $amThanh : null,
                            'Diem' => $diem,
                            'TrangThaiTuVung' => 1
                        ]);
                        
                        if ($result) {
                            $imported++;
                        } else {
                            $skipped++;
                            $errors[] = "Dòng $lineNumber: Lỗi khi insert vào database";
                        }
                    } catch (Exception $e) {
                        $skipped++;
                        $errors[] = "Dòng $lineNumber: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
                
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
    } else {
        $error = "Lỗi upload file: " . $_FILES['csv_file']['error'];
    }
}

// Lấy danh sách khóa học và bài học
$khoaHocList = $Database->get_list("SELECT * FROM khoahoc WHERE TrangThaiKhoaHoc = 1 ORDER BY MaKhoaHoc");
$baiHocList = [];
if (isset($_POST['maKhoaHoc']) && !empty($_POST['maKhoaHoc'])) {
    $maKhoaHoc = intval($_POST['maKhoaHoc']);
    $baiHocList = $Database->get_list("SELECT * FROM baihoc WHERE MaKhoaHoc = $maKhoaHoc AND TrangThaiBaiHoc = 1 ORDER BY MaBaiHoc");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Từ Vựng từ CSV</title>
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
        select, input[type="file"], input[type="checkbox"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }
        select {
            cursor: pointer;
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
        .info h3 {
            margin-top: 0;
        }
        .info code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
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
        <h1>📥 Import Từ Vựng từ CSV</h1>
        
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
            <h3>📋 Hướng dẫn format CSV:</h3>
            <p><strong>Format 1 (tự động tạo bài học từ Part - khuyến nghị):</strong></p>
            <code>Test,Part,NoiDungTuVung,DichNghia,HinhAnh,AmThanh,Diem</code>
            <p>Hoặc:</p>
            <code>Test,Part,Word,Meaning,Image,Audio,Point</code>
            <p><strong>Format 2 (import vào bài học có sẵn):</strong></p>
            <code>NoiDungTuVung,DichNghia,HinhAnh,AmThanh,Diem</code>
            <p><strong>Format 3 (đầy đủ):</strong></p>
            <code>MaTuVung,MaBaiHoc,MaKhoaHoc,NoiDungTuVung,DichNghia,HinhAnh,AmThanh,Diem</code>
            <p><strong>Lưu ý:</strong></p>
            <ul>
                <li>Dòng đầu tiên là header (tên cột)</li>
                <li>Bắt buộc: <code>NoiDungTuVung</code> (hoặc <code>Word</code>, <code>TuVung</code>) và <code>DichNghia</code> (hoặc <code>Meaning</code>, <code>Nghia</code>)</li>
                <li>Nếu bật "Tự động tạo bài học từ Part": CSV phải có cả cột <code>Part</code> và <code>Test</code></li>
                <li>Tên bài học sẽ là: "Part X - Test Y" (mỗi Part+Test = 1 bài học riêng để chia nhỏ từ vựng)</li>
                <li>Tùy chọn: <code>HinhAnh</code>, <code>AmThanh</code>, <code>Diem</code> (mặc định: 10 điểm)</li>
                <li>Nếu bỏ trống <code>HinhAnh</code>, sẽ dùng placeholder image</li>
                <li>Nếu bỏ trống <code>AmThanh</code>, sẽ để NULL</li>
            </ul>
        </div>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="maKhoaHoc">Khóa học *</label>
                <select name="maKhoaHoc" id="maKhoaHoc" required onchange="loadBaiHoc()">
                    <option value="">-- Chọn khóa học --</option>
                    <?php foreach ($khoaHocList as $kh): ?>
                        <option value="<?= $kh['MaKhoaHoc'] ?>" <?= (isset($_POST['maKhoaHoc']) && $_POST['maKhoaHoc'] == $kh['MaKhoaHoc']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($kh['TenKhoaHoc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="autoCreateBaiHoc" id="autoCreateBaiHoc" value="1" <?= (isset($_POST['autoCreateBaiHoc']) && $_POST['autoCreateBaiHoc'] === '1') ? 'checked' : '' ?> onchange="toggleBaiHocSelect()">
                    Tự động tạo bài học từ Part (mỗi Part = 1 bài học)
                </label>
                <small style="color: #666; display: block; margin-top: 5px;">
                    Nếu bật, CSV phải có cả cột "Part" và "Test". Mỗi Part+Test sẽ tạo thành 1 bài học riêng để chia nhỏ từ vựng.
                </small>
            </div>
            
            <div class="form-group" id="baiHocGroup" style="<?= (isset($_POST['autoCreateBaiHoc']) && $_POST['autoCreateBaiHoc'] === '1') ? 'display: none;' : '' ?>">
                <label for="maBaiHoc">Bài học <span id="baiHocRequired">*</span></label>
                <select name="maBaiHoc" id="maBaiHoc">
                    <option value="">-- Chọn bài học --</option>
                    <?php foreach ($baiHocList as $bh): ?>
                        <option value="<?= $bh['MaBaiHoc'] ?>" <?= (isset($_POST['maBaiHoc']) && $_POST['maBaiHoc'] == $bh['MaBaiHoc']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($bh['TenBaiHoc']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="autoMaTuVung" value="1" checked>
                    Tự động tạo MaTuVung (bỏ qua cột MaTuVung trong CSV nếu có)
                </label>
            </div>
            
            <div class="form-group">
                <label for="csv_file">File CSV *</label>
                <input type="file" name="csv_file" id="csv_file" accept=".csv" required>
            </div>
            
            <button type="submit" class="btn">📥 Import CSV</button>
        </form>
        
        <p style="margin-top: 30px;">
            <a href="index.php">← Quay về trang chủ</a>
        </p>
    </div>
    
    <script>
        function loadBaiHoc() {
            const maKhoaHoc = document.getElementById('maKhoaHoc').value;
            const maBaiHocSelect = document.getElementById('maBaiHoc');
            
            if (!maKhoaHoc) {
                maBaiHocSelect.innerHTML = '<option value="">-- Chọn bài học --</option>';
                return;
            }
            
            // Tạo form ẩn để submit và load lại bài học
            const form = document.createElement('form');
            form.method = 'POST';
            form.style.display = 'none';
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'maKhoaHoc';
            input.value = maKhoaHoc;
            form.appendChild(input);
            
            // Giữ lại giá trị autoCreateBaiHoc
            const autoCreateBaiHoc = document.getElementById('autoCreateBaiHoc');
            if (autoCreateBaiHoc && autoCreateBaiHoc.checked) {
                const inputAuto = document.createElement('input');
                inputAuto.type = 'hidden';
                inputAuto.name = 'autoCreateBaiHoc';
                inputAuto.value = '1';
                form.appendChild(inputAuto);
            }
            
            document.body.appendChild(form);
            form.submit();
        }
        
        function toggleBaiHocSelect() {
            const autoCreateBaiHoc = document.getElementById('autoCreateBaiHoc');
            const baiHocGroup = document.getElementById('baiHocGroup');
            const baiHocRequired = document.getElementById('baiHocRequired');
            const maBaiHoc = document.getElementById('maBaiHoc');
            
            if (autoCreateBaiHoc.checked) {
                baiHocGroup.style.display = 'none';
                baiHocRequired.style.display = 'none';
                maBaiHoc.removeAttribute('required');
            } else {
                baiHocGroup.style.display = 'block';
                baiHocRequired.style.display = 'inline';
                maBaiHoc.setAttribute('required', 'required');
            }
        }
        
        // Gọi khi trang load
        window.onload = function() {
            toggleBaiHocSelect();
        };
    </script>
</body>
</html>

