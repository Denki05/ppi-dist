@echo off
cd /d C:\xampp\htdocs\ppi-dist
C:\xampp\php\php.exe artisan schedule:run >> storage\logs\scheduler.log 2>&1