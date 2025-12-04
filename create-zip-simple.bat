@echo off
echo Creating WordPress.org submission ZIP...
echo.

cd /d "E:\onedrive\projects\plugins"

REM Delete old ZIP if exists
if exist "media-inventory-forge.zip" del "media-inventory-forge.zip"

REM Create ZIP using PowerShell (includes folder structure)
powershell -Command "Compress-Archive -Path 'E:\onedrive\projects\plugins\mif' -DestinationPath 'E:\onedrive\projects\plugins\media-inventory-forge-FULL.zip' -CompressionLevel Optimal"

echo.
echo ZIP created at: E:\onedrive\projects\plugins\media-inventory-forge-FULL.zip
echo.
echo IMPORTANT: This ZIP contains ALL files including .git
echo For WordPress.org submission, you should use GitHub URL instead:
echo https://github.com/Mij-Strebor/media-inventory-forge
echo.
pause
