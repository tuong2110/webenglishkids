# Script PowerShell để push code lên GitHub
# https://github.com/tuong2110/poject-1

Write-Host "🚀 Bắt đầu upload code lên GitHub..." -ForegroundColor Green
Write-Host ""

# Kiểm tra Git
Write-Host "📋 Kiểm tra Git..." -ForegroundColor Yellow
$gitVersion = git --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "❌ Git chưa được cài đặt!" -ForegroundColor Red
    Write-Host "Vui lòng cài đặt Git từ: https://git-scm.com/download/win" -ForegroundColor Yellow
    exit
}
Write-Host "✅ $gitVersion" -ForegroundColor Green
Write-Host ""

# Chuyển vào thư mục webhocngoaingu
Set-Location webhocngoaingu

# Kiểm tra Git repository
Write-Host "📋 Kiểm tra Git repository..." -ForegroundColor Yellow
if (-not (Test-Path .git)) {
    Write-Host "⚠️  Chưa có Git repository, đang khởi tạo..." -ForegroundColor Yellow
    git init
    Write-Host "✅ Đã khởi tạo Git repository" -ForegroundColor Green
} else {
    Write-Host "✅ Git repository đã tồn tại" -ForegroundColor Green
}
Write-Host ""

# Kiểm tra remote
Write-Host "📋 Kiểm tra remote repository..." -ForegroundColor Yellow
$remote = git remote get-url origin 2>$null
if ($LASTEXITCODE -ne 0) {
    Write-Host "⚠️  Chưa có remote, đang thêm..." -ForegroundColor Yellow
    git remote add origin https://github.com/tuong2110/poject-1.git
    Write-Host "✅ Đã thêm remote: https://github.com/tuong2110/poject-1.git" -ForegroundColor Green
} else {
    Write-Host "✅ Remote đã tồn tại: $remote" -ForegroundColor Green
    Write-Host "⚠️  Đang cập nhật remote..." -ForegroundColor Yellow
    git remote set-url origin https://github.com/tuong2110/poject-1.git
    Write-Host "✅ Đã cập nhật remote" -ForegroundColor Green
}
Write-Host ""

# Thêm file vào staging
Write-Host "📋 Đang thêm file vào staging..." -ForegroundColor Yellow
git add .
Write-Host "✅ Đã thêm tất cả file" -ForegroundColor Green
Write-Host ""

# Kiểm tra có thay đổi không
$status = git status --porcelain
if ([string]::IsNullOrWhiteSpace($status)) {
    Write-Host "ℹ️  Không có thay đổi để commit" -ForegroundColor Cyan
} else {
    # Commit
    Write-Host "📋 Đang commit code..." -ForegroundColor Yellow
    $commitMessage = "Update: Web học ngoại ngữ với hệ thống game, shop và linh vật"
    git commit -m $commitMessage
    Write-Host "✅ Đã commit code" -ForegroundColor Green
    Write-Host ""
    
    # Push
    Write-Host "📋 Đang push code lên GitHub..." -ForegroundColor Yellow
    Write-Host "⚠️  Lưu ý: Bạn sẽ cần nhập username và Personal Access Token" -ForegroundColor Yellow
    Write-Host ""
    
    git branch -M main
    git push -u origin main
    
    if ($LASTEXITCODE -eq 0) {
        Write-Host ""
        Write-Host "✅ Thành công! Code đã được upload lên GitHub!" -ForegroundColor Green
        Write-Host "🔗 Repository: https://github.com/tuong2110/poject-1" -ForegroundColor Cyan
    } else {
        Write-Host ""
        Write-Host "❌ Lỗi khi push code!" -ForegroundColor Red
        Write-Host "💡 Có thể cần:" -ForegroundColor Yellow
        Write-Host "   1. Tạo Personal Access Token trên GitHub" -ForegroundColor White
        Write-Host "   2. Sử dụng token thay vì password" -ForegroundColor White
        Write-Host "   3. Hoặc pull trước nếu repository đã có code:" -ForegroundColor White
        Write-Host "      git pull origin main --allow-unrelated-histories" -ForegroundColor Cyan
    }
}

Write-Host ""
Write-Host "📝 Hướng dẫn chi tiết xem trong file: PUSH_TO_GITHUB.md" -ForegroundColor Cyan

