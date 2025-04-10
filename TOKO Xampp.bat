@echo off
title Menjalankan Aplikasi Toko...

REM Jalankan Apache
cd /d "C:\xampp\apache\bin"
start "" httpd.exe

REM Jalankan MySQL
cd /d "C:\xampp\mysql\bin"
start "" mysqld.exe

REM Tunggu 5 detik agar server nyala
timeout /t 5 >nul

REM Buka aplikasi di browser
start "" "http://localhost/toko"
