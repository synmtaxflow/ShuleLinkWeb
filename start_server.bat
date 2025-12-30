@echo off
echo ========================================
echo Starting Laravel Server for ZKTeco Push SDK
echo ========================================
echo.
echo Server will listen on: 0.0.0.0:8000
echo This allows device to connect from network
echo.
echo Server IP: 192.168.100.105
echo Server Port: 8000
echo.
echo Push SDK Endpoints:
echo   - GET  http://192.168.100.105:8000/iclock/getrequest
echo   - POST http://192.168.100.105:8000/iclock/cdata
echo.
echo Press Ctrl+C to stop the server
echo ========================================
echo.

php artisan serve --host=0.0.0.0 --port=8000

pause

