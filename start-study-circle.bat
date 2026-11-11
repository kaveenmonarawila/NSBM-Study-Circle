@echo off
title NSBM Study Circle - Local Development Server
echo ===================================================
echo     NSBM STUDY CIRCLE - LAUNCHING SERVER
echo ===================================================
echo.

cd /d "%~dp0"

REM Check for PHP in PATH
where php >nul 2>nul
if %errorlevel% equ 0 (
    set PHP_EXE=php
    goto :START_SERVER
)

REM Check for XAMPP PHP
if exist "C:\xampp\php\php.exe" (
    set PHP_EXE=C:\xampp\php\php.exe
    goto :START_SERVER
)

echo [WARNING] PHP executable was not found. 
echo Opening standalone frontend in default browser...
start "" "%~dp0frontend\index.html"
exit /b

:START_SERVER
echo Starting High-Performance Multi-Worker PHP Server on http://127.0.0.1:8080 ...
echo Press Ctrl+C in this console window to stop the server.
echo.
REM Enable concurrent request handling (prevents single-worker buffering lag)
set PHP_CLI_SERVER_WORKERS=4
start "" "http://127.0.0.1:8080/frontend/index.php"
"%PHP_EXE%" -S 127.0.0.1:8080 -t "%~dp0"
pause
