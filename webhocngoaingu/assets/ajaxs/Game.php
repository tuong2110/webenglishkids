<?php
// Disable output buffering và warnings
ob_start();
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
ini_set('display_errors', 0);

require_once(__DIR__ . "/../../configs/config.php");
require_once(__DIR__ . "/../../configs/function.php");

// Clear any output
ob_clean();

// Set header JSON ngay từ đầu
header('Content-Type: application/json');

if (empty($_POST['type'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Dữ liệu không tồn tại'
    ]);
    exit;
}

// Kiểm tra login nhưng không redirect trong AJAX
if (!isset($_SESSION["account"])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Vui lòng đăng nhập'
    ]);
    exit;
}

// Kiểm tra tài khoản tồn tại
$taiKhoanEscaped = $Database->escape_string($_SESSION["account"]);
$checkAccount = $Database->get_row("SELECT * FROM `nguoidung` WHERE `TaiKhoan` = '$taiKhoanEscaped'");
if (!$checkAccount) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Tài khoản không tồn tại'
    ]);
    exit;
}

$taiKhoan = $taiKhoanEscaped;

// Chọn linh vật
if ($_POST['type'] == 'ChonLinhVat') {
    try {
        if (empty($_POST['maLinhVat'])) {
            throw new Exception("Vui lòng chọn linh vật");
        }
        
        $maLinhVat = intval($_POST['maLinhVat']);
        
        // Kiểm tra linh vật có tồn tại không
        $checkLinhVat = $Database->get_row("SELECT * FROM linhvat WHERE MaLinhVat = $maLinhVat AND TrangThai = 1");
        if (!$checkLinhVat) {
            throw new Exception("Linh vật không tồn tại");
        }
        
        // Kiểm tra đã chọn linh vật chưa
        $checkDaChon = $Database->get_row("SELECT * FROM nguoidung_linhvat WHERE TaiKhoan = '$taiKhoan'");
        if ($checkDaChon) {
            throw new Exception("Bạn đã chọn linh vật rồi");
        }
        
        // Lưu linh vật
        $tenLinhVat = $Database->escape_string($checkLinhVat['TenLinhVat']);
        $Database->connect();
        $result = $Database->query("INSERT INTO nguoidung_linhvat (TaiKhoan, MaLinhVat, TenLinhVat) VALUES ('$taiKhoan', $maLinhVat, '$tenLinhVat')");
        
        if ($result) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => 'Chọn linh vật thành công!'
            ]);
            exit;
        } else {
            $error = $Database->get_error();
            throw new Exception("Không thể lưu linh vật: " . $error);
        }
    } catch (Exception $err) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $err->getMessage()
        ]);
        exit;
    }
}

// Mua vật phẩm
if ($_POST['type'] == 'MuaVatPham') {
    try {
        if (empty($_POST['maVatPham'])) {
            throw new Exception("Vui lòng chọn vật phẩm");
        }
        
        $maVatPham = intval($_POST['maVatPham']);
        
        // Kiểm tra vật phẩm
        $vatPham = $Database->get_row("SELECT * FROM shop_vatpham WHERE MaVatPham = $maVatPham AND TrangThai = 1");
        if (!$vatPham) {
            throw new Exception("Vật phẩm không tồn tại");
        }
        
        // Kiểm tra điểm thưởng
        $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoan'");
        if (!$userDiem) {
            $Database->query("INSERT INTO nguoidung_diemthuong (TaiKhoan, SoDiem) VALUES ('$taiKhoan', 0)");
            $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoan'");
        }
        
        if ($userDiem['SoDiem'] < $vatPham['GiaDiem']) {
            throw new Exception("Bạn không đủ điểm để mua vật phẩm này");
        }
        
        // Trừ điểm
        $soDiemMoi = $userDiem['SoDiem'] - $vatPham['GiaDiem'];
        $Database->query("UPDATE nguoidung_diemthuong SET SoDiem = $soDiemMoi WHERE TaiKhoan = '$taiKhoan'");
        
        // Xử lý vật phẩm
        if ($vatPham['LoaiVatPham'] == 'tim') {
            // Mua tim
            $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoan'");
            if (!$userTim) {
                $Database->query("INSERT INTO nguoidung_tim (TaiKhoan, SoTim) VALUES ('$taiKhoan', 0)");
                $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoan'");
            }
            
            // Tính số tim (1 tim, 5 tim, 10 tim)
            $soTimThem = 1;
            if (strpos($vatPham['TenVatPham'], '5') !== false) {
                $soTimThem = 5;
            } else if (strpos($vatPham['TenVatPham'], '10') !== false) {
                $soTimThem = 10;
            }
            
            $soTimMoi = $userTim['SoTim'] + $soTimThem;
            $Database->query("UPDATE nguoidung_tim SET SoTim = $soTimMoi WHERE TaiKhoan = '$taiKhoan'");
        } else {
            // Mua vật phẩm cho linh vật
            $checkVatPham = $Database->get_row("SELECT * FROM nguoidung_vatpham WHERE TaiKhoan = '$taiKhoan' AND MaVatPham = $maVatPham");
            if ($checkVatPham) {
                $Database->query("UPDATE nguoidung_vatpham SET SoLuong = SoLuong + 1 WHERE TaiKhoan = '$taiKhoan' AND MaVatPham = $maVatPham");
            } else {
                $Database->query("INSERT INTO nguoidung_vatpham (TaiKhoan, MaVatPham, SoLuong) VALUES ('$taiKhoan', $maVatPham, 1)");
            }
        }
        
        // Lưu lịch sử mua hàng
        $tongGia = $vatPham['GiaDiem'];
        $Database->query("INSERT INTO lichsu_muahang (TaiKhoan, MaVatPham, SoLuong, GiaDiem, TongGia) VALUES ('$taiKhoan', $maVatPham, 1, {$vatPham['GiaDiem']}, $tongGia)");
        
        // Lấy lại thông tin mới
        $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoan'");
        $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoan'");
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Mua vật phẩm thành công!',
            'data' => [
                'soTim' => $userTim['SoTim'] ?? 0,
                'soDiem' => $userDiem['SoDiem'] ?? 0
            ]
        ]);
    } catch (Exception $err) {
        echo json_encode([
            'status' => 'error',
            'message' => $err->getMessage()
        ]);
    }
}

// Kết thúc game
if ($_POST['type'] == 'KetThucGame') {
    try {
        if (empty($_POST['maGame']) || empty($_POST['ketQua'])) {
            throw new Exception("Dữ liệu không hợp lệ");
        }
        
        $maGame = intval($_POST['maGame']);
        $ketQua = check_string($_POST['ketQua']);
        $soCauDung = intval($_POST['soCauDung'] ?? 0);
        $tongSoCau = intval($_POST['tongSoCau'] ?? 0);
        
        // Kiểm tra game
        $game = $Database->get_row("SELECT * FROM game WHERE MaGame = $maGame AND TrangThai = 1");
        if (!$game) {
            throw new Exception("Game không tồn tại");
        }
        
        // Kiểm tra tim
        $userTim = $Database->get_row("SELECT * FROM nguoidung_tim WHERE TaiKhoan = '$taiKhoan'");
        if (!$userTim || $userTim['SoTim'] < $game['SoTimCanThiet']) {
            throw new Exception("Bạn không đủ tim");
        }
        
        // Trừ tim
        $soTimMoi = $userTim['SoTim'] - $game['SoTimCanThiet'];
        $Database->query("UPDATE nguoidung_tim SET SoTim = $soTimMoi WHERE TaiKhoan = '$taiKhoan'");
        
        // Xử lý kết quả
        $soDiemNhanDuoc = 0;
        if ($ketQua == 'thang') {
            $soDiemNhanDuoc = $game['DiemThang'];
            
            // Cộng điểm thưởng
            $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoan'");
            if (!$userDiem) {
                $Database->query("INSERT INTO nguoidung_diemthuong (TaiKhoan, SoDiem) VALUES ('$taiKhoan', 0)");
                $userDiem = $Database->get_row("SELECT * FROM nguoidung_diemthuong WHERE TaiKhoan = '$taiKhoan'");
            }
            
            $soDiemMoi = $userDiem['SoDiem'] + $soDiemNhanDuoc;
            $Database->query("UPDATE nguoidung_diemthuong SET SoDiem = $soDiemMoi WHERE TaiKhoan = '$taiKhoan'");
        }
        
        // Lưu lịch sử chơi game
        $Database->query("INSERT INTO lichsu_choigame (TaiKhoan, MaGame, KetQua, SoDiemNhanDuoc, SoTimDaDung) VALUES ('$taiKhoan', $maGame, '$ketQua', $soDiemNhanDuoc, {$game['SoTimCanThiet']})");
        
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'message' => 'Lưu kết quả game thành công!',
            'data' => [
                'ketQua' => $ketQua,
                'soDiemNhanDuoc' => $soDiemNhanDuoc,
                'soTimConLai' => $soTimMoi
            ]
        ]);
        exit;
    } catch (Exception $err) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'message' => $err->getMessage()
        ]);
        exit;
    }
}

