# Hướng Dẫn Upload Code Lên GitHub

## Bước 1: Kiểm tra Git đã cài đặt
```bash
git --version
```

## Bước 2: Khởi tạo Git repository (nếu chưa có)
```bash
cd webhocngoaingu
git init
```

## Bước 3: Thêm remote repository
```bash
git remote add origin https://github.com/tuong2110/poject-1.git
```

Hoặc nếu đã có remote, cập nhật:
```bash
git remote set-url origin https://github.com/tuong2110/poject-1.git
```

## Bước 4: Thêm tất cả file vào staging
```bash
git add .
```

## Bước 5: Commit code
```bash
git commit -m "Initial commit: Web học ngoại ngữ với hệ thống game và linh vật"
```

## Bước 6: Push code lên GitHub
```bash
git branch -M main
git push -u origin main
```

## Lưu ý:
- Nếu repository đã có code, có thể cần pull trước:
  ```bash
  git pull origin main --allow-unrelated-histories
  ```

- Nếu gặp lỗi authentication, cần:
  1. Tạo Personal Access Token trên GitHub
  2. Sử dụng token thay vì password khi push

## Tạo Personal Access Token:
1. Vào GitHub → Settings → Developer settings → Personal access tokens → Tokens (classic)
2. Generate new token
3. Chọn quyền: `repo` (full control)
4. Copy token và dùng khi push

