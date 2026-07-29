<?php

use App\Http\Controllers\Api\FaceScanController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes — Face Recognition Camera Endpoints
|--------------------------------------------------------------------------
| These endpoints are called by the camera device / Python face-recognition
| service to record scan events in real time.
*/

Route::post('/face-scan', [FaceScanController::class, 'process']);
