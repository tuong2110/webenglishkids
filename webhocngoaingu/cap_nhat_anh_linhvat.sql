-- Script cập nhật đường dẫn ảnh linh vật từ URL sang local
-- Chạy script này nếu đã import SQL trước đó
-- Lưu ý: Ảnh 4.png là linh vật, Ảnh 2.png là trang bị/vật phẩm

-- Cập nhật ảnh linh vật (sử dụng ảnh 4.png)
UPDATE `linhvat` SET `AnhDaiDien` = '/assets/img/anhlinhvat/4.png' WHERE `MaLinhVat` = 1;
UPDATE `linhvat` SET `AnhDaiDien` = '/assets/img/anhlinhvat/4.png' WHERE `MaLinhVat` = 2;
UPDATE `linhvat` SET `AnhDaiDien` = '/assets/img/anhlinhvat/4.png' WHERE `MaLinhVat` = 3;

-- Cập nhật ảnh vật phẩm shop
-- Tim → 1.png (ảnh trái tim đỏ)
-- Thức ăn → 3.png
-- Đồ chơi → 4.png
UPDATE `shop_vatpham` SET `AnhDaiDien` = '/assets/img/anhlinhvat/1.png' WHERE `MaVatPham` IN (1, 2, 3);
UPDATE `shop_vatpham` SET `AnhDaiDien` = '/assets/img/anhlinhvat/3.png' WHERE `MaVatPham` = 4;
UPDATE `shop_vatpham` SET `AnhDaiDien` = '/assets/img/anhlinhvat/4.png' WHERE `MaVatPham` = 5;

-- Cập nhật ảnh game (sử dụng ảnh 2.png)
UPDATE `game` SET `AnhDaiDien` = '/assets/img/anhlinhvat/2.png' WHERE `MaGame` IN (1, 2, 3);

