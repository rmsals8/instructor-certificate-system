<?php

use App\Http\Controllers\CareerCertificateController;
use App\Http\Controllers\CareerRecordController;
use App\Http\Controllers\ExcelImportController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SalaryStatementController;
use Illuminate\Support\Facades\Route;

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

// 메인 페이지 및 인증 관련 라우트는 생략 (기본 Laravel 인증 사용)
// 인증 관련 라우트는 Breeze나 Jetstream 설치 시 자동 생성됨

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // 프로필 관리
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 경력 기록 관리
    Route::prefix('career-records')->name('career.records.')->group(function () {
        Route::get('/', [CareerRecordController::class, 'index'])->name('index');
        Route::get('/create', [CareerRecordController::class, 'create'])->name('create');
        Route::post('/', [CareerRecordController::class, 'store'])->name('store');
        Route::get('/{record}/edit', [CareerRecordController::class, 'edit'])->name('edit');
        Route::put('/{record}', [CareerRecordController::class, 'update'])->name('update');
        Route::delete('/{record}', [CareerRecordController::class, 'destroy'])->name('destroy');
    });

    // 경력증명서 관리
    Route::prefix('career-certificates')->name('career.certificates.')->group(function () {
        Route::get('/', [CareerCertificateController::class, 'index'])->name('index');
        Route::get('/create', [CareerCertificateController::class, 'create'])->name('create');
        Route::post('/', [CareerCertificateController::class, 'store'])->name('store');
        Route::get('/{certificate}', [CareerCertificateController::class, 'show'])->name('show');
        Route::get('/{certificate}/download', [CareerCertificateController::class, 'download'])->name('download');
    });

    // 급여명세서 관리
    Route::prefix('salary-statements')->name('salary.statements.')->group(function () {
        Route::get('/', [SalaryStatementController::class, 'index'])->name('index');
        Route::get('/create', [SalaryStatementController::class, 'create'])->name('create');
        Route::post('/', [SalaryStatementController::class, 'store'])->name('store');
        Route::get('/{statement}', [SalaryStatementController::class, 'show'])->name('show');
        Route::post('/{statement}/viewed', [SalaryStatementController::class, 'markAsViewed'])->name('viewed');
        Route::get('/{statement}/download', [SalaryStatementController::class, 'download'])->name('download');
    });

    // 관리자 전용 라우트
    Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
        // 엑셀 업로드
        Route::get('/excel/import', [ExcelImportController::class, 'index'])->name('excel.import');
        Route::post('/excel/import-instructors', [ExcelImportController::class, 'importInstructors'])->name('excel.import.instructors');
        Route::post('/excel/import-career-records', [ExcelImportController::class, 'importCareerRecords'])->name('excel.import.career-records');
    });
});

// Laravel Breeze의 기본 인증 라우트를 사용하지 않는 경우 추가
require __DIR__.'/auth.php';
