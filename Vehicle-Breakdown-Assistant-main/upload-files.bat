@echo off
REM VBAMS Automated Upload Script
REM This uploads all files to InfinityFree via FTP

setlocal enabledelayedexpansion

set FTP_HOST=ftp.gamer.gd
set FTP_USER=if0_42013470
set FTP_PASS=bGOqmJFSHJ
set LOCAL_PATH=C:\xampp\htdocs\final VBAMS - Copy\final VBAMS - Copy\karan clone - Copy\Vehicle-Breakdown-Assistant-main\vehicleassitancems
set REMOTE_PATH=/public_html/vehicleassitancems

echo ========================================
echo VBAMS FTP Upload Tool
echo ========================================
echo.
echo This script will upload all files to InfinityFree
echo Server: %FTP_HOST%
echo User: %FTP_USER%
echo.

REM Create FTP script
(
echo open %FTP_HOST%
echo %FTP_USER%
echo %FTP_PASS%
echo cd public_html
echo mkdir vehicleassitancems
echo cd vehicleassitancems
echo binary
echo lcd "%LOCAL_PATH%"
echo mput *
echo quit
) > ftp_commands.txt

echo Connecting to FTP server...
ftp -s:ftp_commands.txt

if errorlevel 1 (
    echo.
    echo ERROR: FTP upload failed!
    echo.
    echo Alternative: Use FileZilla or Web File Manager
    echo.
) else (
    echo.
    echo SUCCESS: Files uploaded!
    echo.
)

pause
