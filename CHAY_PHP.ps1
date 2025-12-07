# PowerShell script để chạy PHP server
# Encoding: UTF-8

Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "Web Hoc Ngoai Ngu - PHP Server" -ForegroundColor Green
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# Check for XAMPP PHP at D:\Xam
if (Test-Path "D:\Xam\php\php.exe") {
    Write-Host "[OK] Da tim thay PHP tai: D:\Xam\php\php.exe" -ForegroundColor Green
    Write-Host ""
    Write-Host "Server se chay tai: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Nhan Ctrl+C de dung server" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host ""
    Set-Location webhocngoaingu
    & "D:\Xam\php\php.exe" -S localhost:8000 router.php
    exit
}

# Check for XAMPP PHP at C:\xampp
if (Test-Path "C:\xampp\php\php.exe") {
    Write-Host "[OK] Da tim thay PHP tai: C:\xampp\php\php.exe" -ForegroundColor Green
    Write-Host ""
    Write-Host "Server se chay tai: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Nhan Ctrl+C de dung server" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host ""
    Set-Location webhocngoaingu
    & "C:\xampp\php\php.exe" -S localhost:8000 router.php
    exit
}

# Check for WAMP PHP
$wampPhp = Get-ChildItem "C:\wamp64\bin\php" -Directory -ErrorAction SilentlyContinue | 
    Where-Object { Test-Path (Join-Path $_.FullName "php.exe") } | 
    Select-Object -First 1

if ($wampPhp) {
    $phpPath = Join-Path $wampPhp.FullName "php.exe"
    Write-Host "[OK] Da tim thay PHP tai: $phpPath" -ForegroundColor Green
    Write-Host ""
    Write-Host "Server se chay tai: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Nhan Ctrl+C de dung server" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host ""
    Set-Location webhocngoaingu
    & $phpPath -S localhost:8000 router.php
    exit
}

# Check for PHP in PATH
$phpInPath = Get-Command php -ErrorAction SilentlyContinue
if ($phpInPath) {
    Write-Host "[OK] Da tim thay PHP trong PATH: $($phpInPath.Source)" -ForegroundColor Green
    Write-Host ""
    Write-Host "Server se chay tai: http://localhost:8000" -ForegroundColor Yellow
    Write-Host "Nhan Ctrl+C de dung server" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "============================================================" -ForegroundColor Cyan
    Write-Host ""
    Set-Location webhocngoaingu
    php -S localhost:8000 router.php
    exit
}

# No PHP found
Write-Host "[LOI] Khong tim thay PHP!" -ForegroundColor Red
Write-Host ""
Write-Host "Vui long:" -ForegroundColor Yellow
Write-Host "1. Cai dat XAMPP tu: https://www.apachefriends.org/" -ForegroundColor White
Write-Host "2. Hoac cai dat PHP va them vao PATH" -ForegroundColor White
Write-Host ""
Write-Host "Hoac su dung XAMPP Apache:" -ForegroundColor Yellow
Write-Host "1. Mo XAMPP Control Panel" -ForegroundColor White
Write-Host "2. Start Apache" -ForegroundColor White
Write-Host "3. Copy thu muc webhocngoaingu vao C:\xampp\htdocs\" -ForegroundColor White
Write-Host "4. Truy cap: http://localhost/webhocngoaingu" -ForegroundColor White
Write-Host ""
Read-Host "Nhan Enter de thoat"

