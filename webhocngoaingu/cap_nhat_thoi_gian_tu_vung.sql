-- ============================================
-- CẬP NHẬT THỜI GIAN TẠO TỪ VỰNG THÀNH HÔM NAY
-- ============================================

-- Cập nhật tất cả từ vựng có thời gian tạo là hôm nay (ngày hiện tại, giờ random)
UPDATE `tuvung` 
SET `ThoiGianTaoTuVung` = CONCAT(
    CURDATE(), 
    ' ', 
    LPAD(FLOOR(RAND() * 24), 2, '0'), 
    ':', 
    LPAD(FLOOR(RAND() * 60), 2, '0'), 
    ':', 
    LPAD(FLOOR(RAND() * 60), 2, '0')
)
WHERE `TrangThaiTuVung` = 1;

-- Hoặc nếu muốn tất cả cùng một thời gian (ví dụ: 00:00:00 hôm nay)
-- UPDATE `tuvung` 
-- SET `ThoiGianTaoTuVung` = CONCAT(CURDATE(), ' 00:00:00')
-- WHERE `TrangThaiTuVung` = 1;

-- Xem kết quả
SELECT 
    COUNT(*) as 'TongSoTuVung',
    DATE(`ThoiGianTaoTuVung`) as 'NgayTao',
    COUNT(*) as 'SoLuong'
FROM `tuvung`
WHERE `TrangThaiTuVung` = 1
GROUP BY DATE(`ThoiGianTaoTuVung`)
ORDER BY DATE(`ThoiGianTaoTuVung`) DESC;

