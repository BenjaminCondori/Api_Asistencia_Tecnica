<?php

use App\Http\Controllers\AssistanceRequestController;
use App\Http\Controllers\auth\JWTController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\WorkshopController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


// Route::post("register", [ApiController::class, "register"]);
Route::post("login", [JWTController::class, "login"]);

// Route::group(["middleware" => ["auth:api"]], function () {

    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    Route::get('customer', [CustomerController::class, 'index']);
    Route::get('customer/show/{id}', [CustomerController::class, 'show']);
    Route::post('customer/create', [CustomerController::class, 'store']);
    Route::post('customer/update/{id}', [CustomerController::class, 'update']);
    Route::delete('customer/delete/{id}', [CustomerController::class, 'destroy']);

    Route::get('workshop/show/{id}', [WorkshopController::class, 'show']);
    Route::post('workshop/create', [WorkshopController::class, 'store']);
    Route::post('workshop/update/{id}', [WorkshopController::class, 'update']);
    Route::delete('workshop/delete/{id}', [WorkshopController::class, 'destroy']);

    Route::get('technician/show/{id}', [TechnicianController::class, 'show']);
    Route::post('technician/create', [TechnicianController::class, 'store']);
    Route::post('technician/update/{id}', [TechnicianController::class, 'update']);
    Route::delete('technician/delete/{id}', [TechnicianController::class, 'destroy']);

    Route::get('vehicle', [VehicleController::class, 'index']);
    Route::get('vehicle/{id}', [VehicleController::class, 'getVehicles']);
    Route::get('vehicle/show/{id}', [VehicleController::class, 'show']);
    Route::post('vehicle/create', [VehicleController::class, 'store']);
    Route::post('vehicle/update/{id}', [VehicleController::class, 'update']);
    Route::delete('vehicle/delete/{id}', [VehicleController::class, 'destroy']);

    Route::get('assistance-request', [AssistanceRequestController::class, 'index']);
    Route::get('assistance-request/{id}', [AssistanceRequestController::class, 'getAssistanceRequests']);
    Route::get('assistance-request/show/{id}', [AssistanceRequestController::class, 'show']);
    Route::post('assistance-request/create', [AssistanceRequestController::class, 'store']);

// });
