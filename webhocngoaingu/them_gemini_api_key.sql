-- Thêm cột GEMINI_API_KEY vào bảng hethong
-- Chạy script này trong phpMyAdmin hoặc MySQL command line

-- Thêm cột GEMINI_API_KEY (MySQL không hỗ trợ IF NOT EXISTS trong ALTER TABLE)
-- Nếu cột đã tồn tại, sẽ báo lỗi nhưng không sao, bỏ qua lỗi đó
ALTER TABLE `hethong` 
ADD COLUMN `GEMINI_API_KEY` text DEFAULT NULL AFTER `OPENAI_API_KEY`;

-- Sau khi thêm cột, bạn có thể cập nhật giá trị bằng:
-- UPDATE `hethong` SET `GEMINI_API_KEY` = 'AIza...' WHERE `ID` = 1;
-- (Thay 'AIza...' bằng API key thật của bạn từ https://aistudio.google.com/)

