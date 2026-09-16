@echo off
echo ============================================
echo   GPS TRACKING SYSTEM SETUP
echo   Complete Installation Script
echo ============================================
echo.

:: 1. Check PHP version
echo [1] Checking PHP version...
php -v
echo.

:: 2. Install dependencies
echo [2] Installing Composer dependencies...
composer install --no-dev --optimize-autoloader
echo.

echo [3] Installing NPM dependencies...
npm install
echo.

:: 3. Setup environment
echo [4] Copying .env file...
if not exist .env (
    copy .env.example .env
    echo .env file created
) else (
    echo .env file already exists
)
echo.

:: 4. Generate key
echo [5] Generating application key...
php artisan key:generate
echo.

:: 5. Create database
echo [6] Creating database...
mysql -u root -e "CREATE DATABASE IF NOT EXISTS gps_tracking;"
echo Database created/verified
echo.

:: 6. Run migrations
echo [7] Running migrations...
php artisan migrate:fresh --force
echo.

:: 7. Run seeders
echo [8] Running seeders...
php artisan db:seed --class=UserSeeder
php artisan db:seed --class=ItemSeeder
php artisan db:seed --class=GPSTrackingSeeder
echo.

:: 8. Create storage link
echo [9] Creating storage link...
php artisan storage:link
echo.

:: 9. Clear caches
echo [10] Clearing caches...
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
echo.

:: 10. Build assets
echo [11] Building assets...
npm run build
echo.

:: 11. Create admin user
echo [12] Creating admin user...
php artisan tinker --execute="App\Models\User::create(['name'=>'Admin','email'=>'admin@gps.com','password'=>bcrypt('password123'),'role'=>'admin','status'=>'active']);"
echo.

:: 12. Set permissions
echo [13] Setting permissions...
icacls storage /grant Everyone:F /T /Q
icacls bootstrap/cache /grant Everyone:F /T /Q
echo.

echo.
echo ============================================
echo   SETUP COMPLETED SUCCESSFULLY!
echo ============================================
echo.
echo Login Credentials:
echo   Email: admin@gps.com
echo   Password: password123
echo.
echo To start server:
echo   php artisan serve
echo.
echo Or open in browser:
echo   http://localhost:8000
echo.
pause