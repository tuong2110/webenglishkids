-- ============================================
-- CẬP NHẬT HỆ THỐNG LINH VẬT VÀ SHOP
-- ============================================

-- Cập nhật linh vật với tên mới
UPDATE `linhvat` SET 
    `TenLinhVat` = 'Linh Vật Sư Tử',
    `AnhDaiDien` = '/assets/img/anhlinhvat/sutu-cap1.png'
WHERE `MaLinhVat` = 1;

UPDATE `linhvat` SET 
    `TenLinhVat` = 'Linh Vật Bạch Tuộc',
    `AnhDaiDien` = '/assets/img/anhlinhvat/bachtuoc-cap1.png'
WHERE `MaLinhVat` = 2;

UPDATE `linhvat` SET 
    `TenLinhVat` = 'Linh Vật Khỉ',
    `AnhDaiDien` = '/assets/img/anhlinhvat/khi-cap1.png'
WHERE `MaLinhVat` = 3;

-- Xóa vật phẩm cũ trong shop
DELETE FROM `shop_vatpham` WHERE `MaVatPham` IN (1, 2, 3, 4, 5);

-- Thêm vật phẩm mới vào shop
-- Tim (sử dụng icon tim)
INSERT INTO `shop_vatpham` (`MaVatPham`, `TenVatPham`, `LoaiVatPham`, `MoTa`, `GiaDiem`, `AnhDaiDien`, `TrangThai`) VALUES
(1, '1 Tim', 'tim', 'Mua thêm 1 tim để chơi game', 10, 'emoji:❤️', 1),
(2, '5 Tim', 'tim', 'Mua thêm 5 tim để chơi game', 40, 'emoji:❤️', 1),
(3, '10 Tim', 'tim', 'Mua thêm 10 tim để chơi game', 70, 'emoji:❤️', 1);

-- Đồ ăn theo cấp độ
INSERT INTO `shop_vatpham` (`MaVatPham`, `TenVatPham`, `LoaiVatPham`, `MoTa`, `GiaDiem`, `AnhDaiDien`, `TrangThai`) VALUES
(4, 'Đồ Ăn Cấp 1', 'vatpham_linhvat', 'Đồ ăn giúp linh vật tăng 50 XP', 20, '/assets/img/anhlinhvat/doan-cap1.png', 1),
(5, 'Đồ Ăn Cấp 2', 'vatpham_linhvat', 'Đồ ăn giúp linh vật tăng 100 XP', 40, '/assets/img/anhlinhvat/doan-cap2.png', 1),
(6, 'Đồ Ăn Cấp 3', 'vatpham_linhvat', 'Đồ ăn giúp linh vật tăng 200 XP', 80, '/assets/img/anhlinhvat/doan-cap3.png', 1);

-- Đồ chơi theo cấp độ
INSERT INTO `shop_vatpham` (`MaVatPham`, `TenVatPham`, `LoaiVatPham`, `MoTa`, `GiaDiem`, `AnhDaiDien`, `TrangThai`) VALUES
(7, 'Đồ Chơi Cấp 1', 'vatpham_linhvat', 'Đồ chơi giúp linh vật tăng 30 XP', 15, '/assets/img/anhlinhvat/dochoi-1.png', 1),
(8, 'Đồ Chơi Cấp 2', 'vatpham_linhvat', 'Đồ chơi giúp linh vật tăng 60 XP', 30, '/assets/img/anhlinhvat/dochoi-2.png', 1),
(9, 'Đồ Chơi Cấp 3', 'vatpham_linhvat', 'Đồ chơi giúp linh vật tăng 120 XP', 60, '/assets/img/anhlinhvat/dochoi-3.png', 1);

-- Trang bị
INSERT INTO `shop_vatpham` (`MaVatPham`, `TenVatPham`, `LoaiVatPham`, `MoTa`, `GiaDiem`, `AnhDaiDien`, `TrangThai`) VALUES
(10, 'Trang Bị', 'vatpham_linhvat', 'Trang bị giúp linh vật tăng 80 XP', 50, '/assets/img/anhlinhvat/trang-bi.png', 1);

