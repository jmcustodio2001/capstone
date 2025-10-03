@echo off
echo Updating HR2ESS Database...
echo ========================

REM Get database credentials from .env file
for /f "tokens=2 delims==" %%a in ('findstr "DB_DATABASE" .env') do set DB_NAME=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_USERNAME" .env') do set DB_USER=%%a
for /f "tokens=2 delims==" %%a in ('findstr "DB_PASSWORD" .env') do set DB_PASS=%%a

echo Database: %DB_NAME%
echo User: %DB_USER%

REM Execute the complete database fix
mysql -u %DB_USER% -p%DB_PASS% %DB_NAME% < complete_database_fix.sql

if %errorlevel% equ 0 (
    echo.
    echo ✓ Database update completed successfully!
    echo.
    echo Changes applied:
    echo - Removed unnecessary tables
    echo - Fixed PK/FK alignment
    echo - Recreated destination_knowledge_training view
    echo - Optimized all tables
) else (
    echo.
    echo ✗ Database update failed!
    echo Please check the error messages above.
)

pause
