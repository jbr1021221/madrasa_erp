<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\AuthController;

// Auth Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes
Route::middleware(['auth'])->group(function () {
    
    // Common Routes (Admin & Admission Office)
    Route::middleware(['role:admin,admission_office'])->group(function () {
        Route::get('/', function() { return redirect()->route('dashboard'); });
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Students (View, Create, Edit)
        Route::get('/students/generate-id/{classId}', [StudentController::class, 'generateStudentId'])->name('students.generate-id');
        Route::get('/students', [StudentController::class, 'index'])->name('students.index');
        Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
        Route::post('/students', [StudentController::class, 'store'])->name('students.store');
        Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
        Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
        Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
        
        // Student Receipt & Forms
        Route::get('/students/{student}/receipt/confirm', [StudentController::class, 'receiptConfirm'])->name('students.receipt.confirm');
        Route::get('/students/{student}/receipt', [StudentController::class, 'viewReceipt'])->name('students.receipt.view');
        Route::get('/students/{student}/receipt/download', [StudentController::class, 'downloadReceipt'])->name('students.receipt.download');
        Route::get('/students/{student}/admission-form', function($id) {
            $student = \App\Models\Student::findOrFail($id);
            return view('students.admission-form', compact('student'));
        })->name('students.admission-form.view');
        Route::get('/students/{student}/admission-form/download', [StudentController::class, 'downloadAdmissionForm'])->name('students.admission-form.download');

        // Payments
        Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
        Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
        Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
        Route::get('payments/student/{student}/history', [PaymentController::class, 'studentHistory'])
            ->name('payments.student.history');
        Route::get('/payments/{payment}/receipt', [PaymentController::class, 'viewReceipt'])->name('payments.receipt');
        Route::get('/payments/{payment}/receipt/download', [PaymentController::class, 'downloadReceipt'])->name('payments.receipt.download');
        Route::get('/payments/{payment}/edit', [PaymentController::class, 'edit'])->name('payments.edit');
        Route::put('/payments/{payment}', [PaymentController::class, 'update'])->name('payments.update');
        Route::delete('/payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');

        // Classrooms (View Only)
        Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
    });

    // Admin Only Routes
    Route::middleware(['role:admin'])->group(function () {
        // Students (Delete)
        Route::delete('/students/bulk-destroy', [StudentController::class, 'bulkDestroy'])->name('students.bulk-destroy');
        Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

        // Classrooms (Manage)
        Route::get('/classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
        Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
        Route::get('/classrooms/{classroom}/edit', [ClassroomController::class, 'edit'])->name('classrooms.edit');
        Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');
        Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');

        // Accounts
        Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');

        // User Management
        Route::resource('users', \App\Http\Controllers\UserController::class)->except(['show']);
    });
});

Route::get('/phpinfo', function() {
    phpinfo();
});
