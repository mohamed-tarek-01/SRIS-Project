<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\PlateViewController;
use App\Http\Controllers\Web\CarDashboardViewController;
use App\Http\Controllers\Web\CracksViewController;
use App\Http\Controllers\Web\AccidentViewController;
use App\Http\Controllers\Web\FireSmokeViewController;
use App\Http\Controllers\Web\TrafficViewController;
use App\Http\Controllers\Web\VehiclesViewController;
use App\Http\Controllers\Web\CarDamageViewController;

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\AdminUserController;
use App\Http\Controllers\Auth\AdminStationController;

use App\Http\Controllers\Api\PlateController;
use App\Http\Controllers\Api\CracksController;
use App\Http\Controllers\Api\CarDashboardController;
use App\Http\Controllers\Api\AccidentController;
use App\Http\Controllers\Api\FireSmokeController;
use App\Http\Controllers\Api\TrafficController;
use App\Http\Controllers\Api\VehiclesController;
use App\Http\Controllers\Api\CarDamageController;

// User Management Controllers
use App\Http\Controllers\User\VehicleController;
use App\Http\Controllers\User\FuelController;
use App\Http\Controllers\User\MaintenanceController;
use App\Http\Controllers\User\ReminderController;
use App\Http\Controllers\User\DocumentController;
use App\Http\Controllers\User\TripController;
use App\Http\Controllers\User\AnalyticsController;
use App\Http\Controllers\User\UtilityController;


Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
    Route::get('/verify-otp', [AuthController::class, 'showVerifyOtp'])->name('verify.otp');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Car Management Routes
        Route::prefix('user')->name('user.')->group(function () {
            // Vehicles
            Route::resource('vehicles', VehicleController::class);
            
            // Fuel
            Route::resource('fuel', FuelController::class);
            
            // Maintenance
            Route::resource('maintenance', MaintenanceController::class);
            
            // Reminders
            Route::resource('reminders', ReminderController::class);
            Route::patch('reminders/{reminder}/toggle', [ReminderController::class, 'toggleComplete'])->name('reminders.toggle');
            
            // Documents
            Route::resource('documents', DocumentController::class);
            
            // Analytics
            Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics');
            
            // Utilities
            Route::get('stations', [UtilityController::class, 'stations'])->name('stations');
            Route::get('fines', [UtilityController::class, 'fines'])->name('fines');
            
            // Trips
            Route::resource('trips', TripController::class);

            // Wallet & Payments
            Route::get('wallet', [\App\Http\Controllers\User\WalletController::class, 'index'])->name('wallet.index');
            Route::post('wallet/upload', [\App\Http\Controllers\User\WalletController::class, 'uploadReceipt'])->name('wallet.upload');

            // Drive Mode (Geofencing)
            Route::get('drive', [\App\Http\Controllers\User\DriveModeController::class, 'index'])->name('drive.index');
        });

        // Role-based models: Admin can't access User models, and Users can't access Admin tools.
        
        // Admin Paths (Everything EXCEPT user models)
        Route::middleware('role:admin')->group(function () {
            Route::get('/plate', [PlateViewController::class, 'index'])->name('plate');
            Route::get('/cracks', [CracksViewController::class, 'index'])->name('cracks');
            Route::get('/accident', [AccidentViewController::class, 'index'])->name('accident');
            Route::get('/fire_smoke', [FireSmokeViewController::class, 'index'])->name('fire_smoke');
            Route::get('/vehicles', [VehiclesViewController::class, 'index'])->name('vehicles');
            Route::get('/car_damage', [CarDamageViewController::class, 'index'])->name('car_damage');

            // User Management CRUD
            Route::resource('admin/users', AdminUserController::class)->names('admin.users');

            // Station Management CRUD
            Route::resource('admin/stations', AdminStationController::class)->names('admin.stations');

            // Admin Payments
            Route::get('admin/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');
            Route::post('admin/payments/{payment}/approve', [\App\Http\Controllers\Admin\PaymentController::class, 'approve'])->name('admin.payments.approve');
            Route::post('admin/payments/{payment}/reject', [\App\Http\Controllers\Admin\PaymentController::class, 'reject'])->name('admin.payments.reject');
        });

        // User Paths (Two models)
        Route::middleware('role:user')->group(function () {
            Route::get('/car_dashboard', [CarDashboardViewController::class, 'index'])->name('car_dashboard');
            Route::get('/traffic', [TrafficViewController::class, 'index'])->name('traffic');
        });
});

// ML Detection Routes (Moved from api.php to allow session access)
Route::middleware(['auth', 'web'])->prefix('api/ml')->group(function () {
    Route::post('/plate/detect', [PlateController::class, 'detect']);
    Route::post('/cracks/detect', [CracksController::class, 'detect']);
    Route::post('/car_dashboard/detect', [CarDashboardController::class, 'detect']);
    Route::post('/accident/detect', [AccidentController::class, 'detect']);
    Route::post('/fire_smoke/detect', [FireSmokeController::class, 'detect']);
    Route::post('/traffic/detect', [TrafficController::class, 'detect']);
    Route::post('/vehicles/detect', [VehiclesController::class, 'detect']);
    Route::post('/car_damage/detect', [CarDamageController::class, 'detect']);
});

// Hazard Geofencing Route
Route::middleware(['auth', 'web'])->get('/api/hazards/nearby', [\App\Http\Controllers\Api\HazardController::class, 'nearby'])->name('hazards.nearby');

// Dynamic Station Routes
Route::middleware(['auth', 'web', 'role:admin'])->prefix('api/stations')->group(function () {
    Route::get('/check-location', [\App\Http\Controllers\Api\StationApiController::class, 'checkLocation'])->name('api.stations.check');
    Route::post('/create-dynamic', [\App\Http\Controllers\Api\StationApiController::class, 'createDynamic'])->name('api.stations.create_dynamic');
});
