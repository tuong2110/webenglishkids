-- ============================================
-- HỆ THỐNG GAME VÀ LINH VẬT
-- ============================================

-- Bảng lưu thông tin linh vật
CREATE TABLE IF NOT EXISTS `linhvat` (
  `MaLinhVat` int(11) NOT NULL AUTO_INCREMENT,
  `TenLinhVat` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `AnhDaiDien` text NOT NULL,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaLinhVat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm 3 linh vật mặc định (sử dụng ảnh 4.png)
INSERT INTO `linhvat` (`MaLinhVat`, `TenLinhVat`, `MoTa`, `AnhDaiDien`, `TrangThai`) VALUES
(1, 'Linh Vật Học Tập', 'Linh vật thông minh, giúp bạn học tập hiệu quả', '/assets/img/anhlinhvat/4.png', 1),
(2, 'Linh Vật Năng Động', 'Linh vật năng động, luôn sẵn sàng thử thách', '/assets/img/anhlinhvat/4.png', 1),
(3, 'Linh Vật Sáng Tạo', 'Linh vật sáng tạo, khơi nguồn cảm hứng', '/assets/img/anhlinhvat/4.png', 1);

-- Bảng lưu linh vật của người dùng
CREATE TABLE IF NOT EXISTS `nguoidung_linhvat` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaLinhVat` int(11) NOT NULL,
  `TenLinhVat` varchar(100) DEFAULT NULL,
  `CapDo` int(11) NOT NULL DEFAULT 1,
  `KinhNghiem` int(11) NOT NULL DEFAULT 0,
  `ThoiGianChon` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`TaiKhoan`),
  KEY `FK_linhvat_nguoidung_linhvat` (`MaLinhVat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu tim của người dùng (mỗi tài khoản có 5 tim ban đầu)
CREATE TABLE IF NOT EXISTS `nguoidung_tim` (
  `TaiKhoan` varchar(100) NOT NULL,
  `SoTim` int(11) NOT NULL DEFAULT 5,
  `ThoiGianCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`TaiKhoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu điểm thưởng của người dùng
CREATE TABLE IF NOT EXISTS `nguoidung_diemthuong` (
  `TaiKhoan` varchar(100) NOT NULL,
  `SoDiem` int(11) NOT NULL DEFAULT 0,
  `ThoiGianCapNhat` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`TaiKhoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu vật phẩm trong shop
CREATE TABLE IF NOT EXISTS `shop_vatpham` (
  `MaVatPham` int(11) NOT NULL AUTO_INCREMENT,
  `TenVatPham` varchar(100) NOT NULL,
  `LoaiVatPham` enum('tim','vatpham_linhvat') NOT NULL,
  `MoTa` text DEFAULT NULL,
  `GiaDiem` int(11) NOT NULL,
  `AnhDaiDien` text NOT NULL,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaVatPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm vật phẩm mặc định vào shop
-- Tim → 1.png (ảnh trái tim đỏ)
-- Thức ăn → 3.png
-- Đồ chơi → 4.png
-- Trang bị → 2.png
INSERT INTO `shop_vatpham` (`MaVatPham`, `TenVatPham`, `LoaiVatPham`, `MoTa`, `GiaDiem`, `AnhDaiDien`, `TrangThai`) VALUES
(1, '1 Tim', 'tim', 'Mua thêm 1 tim để chơi game', 10, '/assets/img/anhlinhvat/1.png', 1),
(2, '5 Tim', 'tim', 'Mua thêm 5 tim để chơi game', 40, '/assets/img/anhlinhvat/1.png', 1),
(3, '10 Tim', 'tim', 'Mua thêm 10 tim để chơi game', 70, '/assets/img/anhlinhvat/1.png', 1),
(4, 'Thức Ăn Cho Linh Vật', 'vatpham_linhvat', 'Thức ăn giúp linh vật tăng kinh nghiệm', 20, '/assets/img/anhlinhvat/3.png', 1),
(5, 'Đồ Chơi Cho Linh Vật', 'vatpham_linhvat', 'Đồ chơi giúp linh vật vui vẻ hơn', 30, '/assets/img/anhlinhvat/4.png', 1);

-- Bảng lưu vật phẩm của người dùng
CREATE TABLE IF NOT EXISTS `nguoidung_vatpham` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaVatPham` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL DEFAULT 1,
  `ThoiGianMua` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`TaiKhoan`, `MaVatPham`),
  KEY `FK_shop_vatpham_nguoidung_vatpham` (`MaVatPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu lịch sử mua hàng
CREATE TABLE IF NOT EXISTS `lichsu_muahang` (
  `MaLichSu` int(11) NOT NULL AUTO_INCREMENT,
  `TaiKhoan` varchar(100) NOT NULL,
  `MaVatPham` int(11) NOT NULL,
  `SoLuong` int(11) NOT NULL DEFAULT 1,
  `GiaDiem` int(11) NOT NULL,
  `TongGia` int(11) NOT NULL,
  `ThoiGianMua` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaLichSu`),
  KEY `FK_taikhoan_lichsu_muahang` (`TaiKhoan`),
  KEY `FK_vatpham_lichsu_muahang` (`MaVatPham`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Bảng lưu thông tin game
CREATE TABLE IF NOT EXISTS `game` (
  `MaGame` int(11) NOT NULL AUTO_INCREMENT,
  `TenGame` varchar(100) NOT NULL,
  `MoTa` text DEFAULT NULL,
  `AnhDaiDien` text NOT NULL,
  `SoTimCanThiet` int(11) NOT NULL DEFAULT 1,
  `DiemThang` int(11) NOT NULL DEFAULT 10,
  `DiemThua` int(11) NOT NULL DEFAULT 0,
  `TrangThai` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTao` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaGame`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm game mặc định (sử dụng ảnh 2.png)
INSERT INTO `game` (`MaGame`, `TenGame`, `MoTa`, `AnhDaiDien`, `SoTimCanThiet`, `DiemThang`, `DiemThua`, `TrangThai`) VALUES
(1, 'Game Đoán Từ', 'Đoán từ vựng tiếng Anh', '/assets/img/anhlinhvat/2.png', 1, 10, 0, 1),
(2, 'Game Nối Từ', 'Nối từ với nghĩa đúng', '/assets/img/anhlinhvat/2.png', 1, 15, 0, 1),
(3, 'Game Trắc Nghiệm', 'Trả lời câu hỏi trắc nghiệm', '/assets/img/anhlinhvat/2.png', 1, 20, 0, 1);

-- Bảng lưu lịch sử chơi game
CREATE TABLE IF NOT EXISTS `lichsu_choigame` (
  `MaLichSu` int(11) NOT NULL AUTO_INCREMENT,
  `TaiKhoan` varchar(100) NOT NULL,
  `MaGame` int(11) NOT NULL,
  `KetQua` enum('thang','thua') NOT NULL,
  `SoDiemNhanDuoc` int(11) NOT NULL DEFAULT 0,
  `SoTimDaDung` int(11) NOT NULL DEFAULT 1,
  `ThoiGianChoi` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaLichSu`),
  KEY `FK_taikhoan_lichsu_choigame` (`TaiKhoan`),
  KEY `FK_game_lichsu_choigame` (`MaGame`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm foreign keys
ALTER TABLE `nguoidung_linhvat`
  ADD CONSTRAINT `FK_linhvat_nguoidung_linhvat` FOREIGN KEY (`MaLinhVat`) REFERENCES `linhvat` (`MaLinhVat`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_taikhoan_nguoidung_linhvat` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `nguoidung_tim`
  ADD CONSTRAINT `FK_taikhoan_nguoidung_tim` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `nguoidung_diemthuong`
  ADD CONSTRAINT `FK_taikhoan_nguoidung_diemthuong` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `nguoidung_vatpham`
  ADD CONSTRAINT `FK_taikhoan_nguoidung_vatpham` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_shop_vatpham_nguoidung_vatpham` FOREIGN KEY (`MaVatPham`) REFERENCES `shop_vatpham` (`MaVatPham`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `lichsu_muahang`
  ADD CONSTRAINT `FK_taikhoan_lichsu_muahang` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_vatpham_lichsu_muahang` FOREIGN KEY (`MaVatPham`) REFERENCES `shop_vatpham` (`MaVatPham`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `lichsu_choigame`
  ADD CONSTRAINT `FK_taikhoan_lichsu_choigame` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_game_lichsu_choigame` FOREIGN KEY (`MaGame`) REFERENCES `game` (`MaGame`) ON DELETE CASCADE ON UPDATE CASCADE;

