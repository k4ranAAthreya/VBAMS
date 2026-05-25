@echo off
REM VBAMS Upload Launcher
REM Just double-click this file to upload everything!

echo.
echo ========================================
echo VBAMS Automated Upload Tool
echo ========================================
echo.
echo This will upload all files to InfinityFree
echo Server: ftp.gamer.gd
echo.
pause

REM Run PowerShell script
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0upload.ps1"

pause
