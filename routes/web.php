<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ClassroomController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AccountController;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');


// Or if you have a DashboardController:
// Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Classroom routes
Route::get('/classrooms', [ClassroomController::class, 'index'])->name('classrooms.index');
Route::get('/classrooms/create', [ClassroomController::class, 'create'])->name('classrooms.create');
Route::post('/classrooms', [ClassroomController::class, 'store'])->name('classrooms.store');
Route::get('/classrooms/{classroom}/edit', [ClassroomController::class, 'edit'])->name('classrooms.edit');
Route::put('/classrooms/{classroom}', [ClassroomController::class, 'update'])->name('classrooms.update');
Route::delete('/classrooms/{classroom}', [ClassroomController::class, 'destroy'])->name('classrooms.destroy');

// Students routes
Route::get('/students', [StudentController::class, 'index'])->name('students.index');
Route::get('/students/create', [StudentController::class, 'create'])->name('students.create');
Route::post('/students', [StudentController::class, 'store'])->name('students.store');
Route::get('/students/{student}', [StudentController::class, 'show'])->name('students.show');
Route::get('/students/{student}/edit', [StudentController::class, 'edit'])->name('students.edit');
Route::put('/students/{student}', [StudentController::class, 'update'])->name('students.update');
Route::delete('/students/{student}', [StudentController::class, 'destroy'])->name('students.destroy');

// Payments routes
Route::get('/payments', [PaymentController::class, 'index'])->name('payments.index');
Route::get('/payments/create', [PaymentController::class, 'create'])->name('payments.create');
Route::post('/payments', [PaymentController::class, 'store'])->name('payments.store');
// Payments routes

Route::get('payments/student/{student}/history', [PaymentController::class, 'studentHistory'])
    ->name('payments.student.history');
// Accounts routes (add if needed)
Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');