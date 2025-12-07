@echo off
chcp 65001 >nul 2>&1
echo ============================================================
echo Web Hoc Ngoai Ngu - PHP Server
echo ============================================================
echo.

REM Check for XAMPP PHP at D:\Xam
if exist "D:\Xam\php\php.exe" (
    echo [OK] Da tim thay PHP tai: D:\Xam\php\php.exe
    echo.
    echo Server se chay tai: http://localhost:8000
    echo Nhan Ctrl+C de dung server
    echo.
    echo ============================================================
    echo.
    cd webhocngoaingu
    D:\Xam\php\php.exe -S localhost:8000 router.php
    goto :end
)

REM Check for XAMPP PHP at C:\xampp
if exist "C:\xampp\php\php.exe" (
    echo [OK] Da tim thay PHP tai: C:\xampp\php\php.exe
    echo.
    echo Server se chay tai: http://localhost:8000
    echo Nhan Ctrl+C de dung server
    echo.
    echo ============================================================
    echo.
    cd webhocngoaingu
    C:\xampp\php\php.exe -S localhost:8000 router.php
    goto :end
)

REM Check for WAMP PHP
for /d %%i in (C:\wamp64\bin\php\php*) do (
    if exist "%%i\php.exe" (
        echo [OK] Da tim thay PHP tai: %%i\php.exe
        echo.
        echo Server se chay tai: http://localhost:8000
        echo Nhan Ctrl+C de dung server
        echo.
        echo ============================================================
        echo.
        cd webhocngoaingu
        "%%i\php.exe" -S localhost:8000 router.php
        goto :end
    )
)

echo [LOI] Khong tim thay PHP!
echo.
echo Vui long:
echo 1. Cai dat XAMPP tu: https://www.apachefriends.org/
echo 2. Hoac cai dat PHP va them vao PATH
echo.
echo Hoac su dung XAMPP Apache:
echo 1. Mo XAMPP Control Panel
echo 2. Start Apache
echo 3. Copy thu muc webhocngoaingu vao C:\xampp\htdocs\
echo 4. Truy cap: http://localhost/webhocngoaingu
echo.
pause

:end

