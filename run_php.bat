@echo off
echo ============================================================
echo Web Hoc Ngoai Ngu - PHP Server
echo ============================================================
echo.

REM Check for XAMPP PHP
if exist "C:\xampp\php\php.exe" (
    set PHP_PATH=C:\xampp\php\php.exe
    echo [OK] Found PHP at: C:\xampp\php\php.exe
    goto :run_server
)

REM Check for WAMP PHP
for /d %%i in (C:\wamp64\bin\php\php*) do (
    if exist "%%i\php.exe" (
        set PHP_PATH=%%i\php.exe
        echo [OK] Found PHP at: %%i\php.exe
        goto :run_server
    )
)

REM Check if PHP is in PATH
where php >nul 2>&1
if %errorlevel% == 0 (
    set PHP_PATH=php
    echo [OK] Found PHP in PATH
    goto :run_server
)

echo [ERROR] PHP not found!
echo.
echo Please install PHP or XAMPP/WAMP
echo Or add PHP to your system PATH
echo.
pause
exit /b 1

:run_server
echo.
echo Server will run at: http://localhost:8000
echo Press Ctrl+C to stop server
echo.
echo ============================================================
echo.

cd webhocngoaingu
%PHP_PATH% -S localhost:8000


