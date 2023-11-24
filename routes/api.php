<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\TechnicianController;
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
// Route::post("login", [ApiController::class, "login"]);

// Route::group(["middleware" => ["auth:api"]], function () {

    Route::get("profile", [ApiController::class, "profile"]);
    Route::get("refresh", [ApiController::class, "refreshToken"]);
    Route::get("logout", [ApiController::class, "logout"]);

    Route::get('customer/show/{id}', [CustomerController::class, 'show']);
    Route::post('customer', [CustomerController::class, 'store']);
    Route::put('customer/update/{id}', [CustomerController::class, 'update']);
    Route::delete('customer/delete/{id}', [CustomerController::class, 'destroy']);

    Route::get('workshop/show/{id}', [WorkshopController::class, 'show']);
    Route::post('workshop', [WorkshopController::class, 'store']);
    Route::put('workshop/update/{id}', [WorkshopController::class, 'update']);
    Route::delete('workshop/delete/{id}', [WorkshopController::class, 'destroy']);

    Route::get('technician/show/{id}', [TechnicianController::class, 'show']);
    Route::post('technician', [TechnicianController::class, 'store']);
    Route::put('technician/update/{id}', [TechnicianController::class, 'update']);
    Route::delete('technician/delete/{id}', [TechnicianController::class, 'destroy']);

// });
