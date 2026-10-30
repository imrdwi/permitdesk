<?php

use App\Http\Controllers\Applicant\ApplicationController;
use App\Http\Controllers\Applicant\DocumentController;
use App\Http\Controllers\Applicant\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\Office\ApplicationController as OfficeApplicationController;
use App\Http\Controllers\Office\DecisionController;
use App\Http\Controllers\Office\ReportController;
use App\Http\Controllers\Office\StaffController;
use App\Http\Controllers\PermitPrintController;
use App\Http\Controllers\VerificationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard')->name('home');

Route::get('verify/{code}', VerificationController::class)->middleware('throttle:30,1')->name('permits.verify');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('documents/{document}', DocumentDownloadController::class)->name('documents.download');
    Route::get('permits/{application}/print', PermitPrintController::class)->name('permits.print');

    Route::prefix('my')->name('applicant.')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::get('documents', [DocumentController::class, 'index'])->name('documents.index');
        Route::post('documents', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('documents/{document}', [DocumentController::class, 'destroy'])->name('documents.destroy');
    });

    Route::resource('applications', ApplicationController::class);

    Route::prefix('office')->name('office.')->middleware('can:office')->group(function () {
        Route::get('applications', [OfficeApplicationController::class, 'index'])->name('applications.index');
        Route::get('applications/{application}', [OfficeApplicationController::class, 'show'])->name('applications.show');
        Route::delete('applications/{application}', [OfficeApplicationController::class, 'destroy'])->name('applications.destroy');
        Route::post('applications/{application}/review', [DecisionController::class, 'review'])->name('applications.review');
        Route::post('applications/{application}/approve', [DecisionController::class, 'approve'])->name('applications.approve');
        Route::post('applications/{application}/reject', [DecisionController::class, 'reject'])->name('applications.reject');

        Route::middleware('can:head')->group(function () {
            Route::get('report', [ReportController::class, 'index'])->name('report');
            Route::resource('staff', StaffController::class)->except('show')->parameters(['staff' => 'user']);
        });
    });
});

require __DIR__.'/settings.php';
