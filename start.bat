@echo off
title نمایشگاه پروژه‌ها
echo ============================================
echo    نمایشگاه پروژه‌های توسعه وب
echo ============================================
echo.
echo 🔧 در حال راه‌اندازی سرور محلی...
echo.
python -m http.server 5500 -d "%~dp0"
if %errorlevel% neq 0 (
    echo Python پیدا نشد! لطفاً Python را نصب کنید.
    pause
)