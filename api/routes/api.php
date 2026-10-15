<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\EnquiryController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\OverviewController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\VenueController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/overview', [OverviewController::class, 'show']);
    Route::get('/events', [EventController::class, 'index']);
    Route::get('/vendors', [VendorController::class, 'index']);
    Route::get('/companies/all', [CompanyController::class, 'all']);
    Route::get('/companies', [CompanyController::class, 'index']);
    Route::get('/venues', [VenueController::class, 'index']);
    Route::get('/enquiries', [EnquiryController::class, 'index']);
});
