<?php

use App\Http\Controllers\Api\FaceScanController;
use App\Http\Controllers\Api\FaceDescriptorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Face Recognition Camera Endpoints
|--------------------------------------------------------------------------
*/

Route::post('/face-scan', [FaceScanController::class, 'process']);

// Returns registered face images for client-side matching
Route::get('/face-descriptors', [FaceDescriptorController::class, 'index']);
