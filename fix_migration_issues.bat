@echo off
echo Fixing migration issues...
php final_migration_fix.php
echo.
echo Running fresh migration and seeding...
php artisan migrate:fresh --seed
echo.
echo Migration fix completed!