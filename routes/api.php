<?php

use App\Http\Controllers\Api\ContactController;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Contacts API Routes with Rate Limiting
Route::group(['middleware' => 'rate.limit:default'], function () {
    // Read operations (higher limits)
    Route::get('contacts', [ContactController::class, 'index']);
    Route::get('contacts/{contact}', [ContactController::class, 'show']);
});

Route::group(['middleware' => 'rate.limit:strict'], function () {
    // Write operations (stricter limits)
    Route::post('contacts', [ContactController::class, 'store']);
    Route::put('contacts/{contact}', [ContactController::class, 'update']);
    Route::patch('contacts/{contact}', [ContactController::class, 'update']);
    Route::delete('contacts/{contact}', [ContactController::class, 'destroy']);
}); 