<?php
require_once("../../configs/config.php");
require_once("../../configs/function.php");

echo "=== KIỂM TRA HỆ THỐNG EMAIL ===\n\n";

// Kiểm tra số lượng người dùng có bật báo cáo tiến trình
$countBaoCao = $Database->num_rows("SELECT * FROM thongbaoemail A 
    INNER JOIN nguoidung B ON A.TaiKhoan = B.TaiKhoan 
    WHERE B.Email IS NOT NULL AND A.BaoCaoTienTrinhHocTap = 1");
echo "Số người dùng bật báo cáo tiến trình: $countBaoCao\n";

// Kiểm tra số lượng người dùng có bật nhắc nhở
$countNhacNho = $Database->num_rows("SELECT * FROM thongbaoemail A 
    INNER JOIN nguoidung B ON A.TaiKhoan = B.TaiKhoan 
    WHERE B.Email IS NOT NULL AND A.NhacNhoTienTrinhHocTap = 1");
echo "Số người dùng bật nhắc nhở học tập: $countNhacNho\n\n";

// Kiểm tra dữ liệu học tập hôm nay
$currentDate = date("Y-m-d");
$countHocTap = $Database->num_rows("SELECT * FROM hoctuvung WHERE DATE(THOIGIAN) = '$currentDate'");
echo "Số từ vựng đã học hôm nay ($currentDate): $countHocTap\n\n";

// Chạy script báo cáo
echo "=== CHẠY BÁO CÁO TIẾN TRÌNH HỌC TẬP ===\n";
include("bao_cao_hoc_tap.php");
echo "\n";

// Chạy script nhắc nhở
echo "=== CHẠY NHẮC NHỞ HỌC TẬP ===\n";
include("nhac_nho_hoc_tap.php");
echo "\n";

echo "=== HOÀN TẤT ===\n";

