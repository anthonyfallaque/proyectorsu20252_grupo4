<?php

use App\Http\Controllers\admin\BrandmodelController;
use App\Http\Controllers\admin\AttendanceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\admin\VehicleController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});






Route::get('/get-models/{brand_id}', [BrandModelController::class, 'getModelsByBrand'])->name('admin.getModelsByBrand');

Route::get('/getimages/{vehicle_id}', [VehicleController::class, 'getImages'])->name('admin.getimages');
Route::post('/set-profile/{image_id}', [VehicleController::class, 'setProfile'])->name('admin.vehicles.setProfile');
Route::delete('/delete-image/{image_id}', [VehicleController::class, 'deleteImage'])->name('admin.vehicles.deleteImage');
Route::post('/store-image', [VehicleController::class, 'storeImages'])->name('admin.vehicles.storeImages');

// Rutas para Turnos
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class);
});

// Rutas para Empleados
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
});

Route::post('attendances/store', [AttendanceController::class, 'storeAttendance'])->name('attendances.storeAttendance');
Route::get('attendances', [AttendanceController::class, 'indexAttendance'])->name('attendances.indexAttendance');

Route::get('admin/zones/{zone}/ajax', [App\Http\Controllers\Admin\ZoneController::class, 'getZoneAjax'])->name('admin.zones.ajax');
// Rutas para el módulo de Empleados
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('employees', App\Http\Controllers\Admin\EmployeeController::class);
    Route::post('employees/check-unique', [App\Http\Controllers\Admin\EmployeeController::class, 'checkUnique']);
});

// Rutas para el módulo de Tipos de Empleados
Route::prefix('admin')->name('admin.')->group(function () {
    Route::post('employee-types/check-unique', [App\Http\Controllers\Admin\EmployeeTypeController::class, 'checkUnique']);
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('employee-types', App\Http\Controllers\Admin\EmployeeTypeController::class);
    Route::post('employee-types/check-unique', [App\Http\Controllers\Admin\EmployeeTypeController::class, 'checkUnique']);
});
});

// Rutas para el módulo de Turnos
Route::prefix('admin')->name('admin.')->group(function () {
    Route::resource('shifts', App\Http\Controllers\Admin\ShiftController::class);
    Route::post('shifts/check-unique', [App\Http\Controllers\Admin\ShiftController::class, 'checkUnique']);
});
