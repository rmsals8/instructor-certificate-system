<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CareerCertificateController;
use App\Http\Controllers\API\CareerRecordController;
use App\Http\Controllers\API\SalaryStatementController;
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

// 인증 라우트
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 인증 필요 라우트
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // 경력 기록 API
    Route::apiResource('career-records', CareerRecordController::class);

    // 경력증명서 API
    Route::get('/career-certificates', [CareerCertificateController::class, 'index']);
    Route::get('/career-certificates/{certificate}', [CareerCertificateController::class, 'show']);
    Route::get('/career-certificates/{certificate}/download', [CareerCertificateController::class, 'download']);

    // 급여명세서 API
    Route::get('/salary-statements', [SalaryStatementController::class, 'index']);
    Route::get('/salary-statements/{statement}', [SalaryStatementController::class, 'show']);
    Route::get('/salary-statements/{statement}/download', [SalaryStatementController::class, 'download']);

    // 관리자 전용 API
    Route::middleware('admin')->prefix('admin')->group(function () {
        // 경력증명서 발급
        Route::post('/career-certificates', [CareerCertificateController::class, 'store']);

        // 급여명세서 발급
        Route::post('/salary-statements', [SalaryStatementController::class, 'store']);
    });
});
