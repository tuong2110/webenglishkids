-- Tạo bảng chặng (stage/chapter)
CREATE TABLE IF NOT EXISTS `chang` (
  `MaChang` int(11) NOT NULL AUTO_INCREMENT,
  `MaKhoaHoc` int(11) NOT NULL,
  `TenChang` varchar(255) NOT NULL,
  `MoTaChang` text DEFAULT NULL,
  `ThuTuChang` int(11) NOT NULL DEFAULT 1,
  `HinhAnhChang` text DEFAULT NULL,
  `TrangThaiChang` tinyint(1) NOT NULL DEFAULT 1,
  `ThoiGianTaoChang` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`MaChang`),
  KEY `FK_khoahoc_chang` (`MaKhoaHoc`),
  CONSTRAINT `FK_khoahoc_chang` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Thêm cột MaChang vào bảng baihoc
ALTER TABLE `baihoc` 
ADD COLUMN `MaChang` int(11) DEFAULT NULL AFTER `MaKhoaHoc`,
ADD KEY `FK_chang_baihoc` (`MaChang`),
ADD CONSTRAINT `FK_chang_baihoc` FOREIGN KEY (`MaChang`) REFERENCES `chang` (`MaChang`) ON DELETE SET NULL;

-- Tạo bảng theo dõi tiến độ hoàn thành chặng
CREATE TABLE IF NOT EXISTS `hoanthanhchang` (
  `TaiKhoan` varchar(100) NOT NULL,
  `MaChang` int(11) NOT NULL,
  `MaKhoaHoc` int(11) NOT NULL,
  `ThoiGianHoanThanh` datetime NOT NULL DEFAULT current_timestamp(),
  `TiLeHoanThanh` decimal(5,2) NOT NULL DEFAULT 0.00,
  PRIMARY KEY (`TaiKhoan`, `MaChang`),
  KEY `FK_chang_hoanthanh` (`MaChang`),
  KEY `FK_khoahoc_hoanthanh` (`MaKhoaHoc`),
  CONSTRAINT `FK_chang_hoanthanh` FOREIGN KEY (`MaChang`) REFERENCES `chang` (`MaChang`),
  CONSTRAINT `FK_khoahoc_hoanthanh` FOREIGN KEY (`MaKhoaHoc`) REFERENCES `khoahoc` (`MaKhoaHoc`),
  CONSTRAINT `FK_taikhoan_hoanthanh` FOREIGN KEY (`TaiKhoan`) REFERENCES `nguoidung` (`TaiKhoan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


