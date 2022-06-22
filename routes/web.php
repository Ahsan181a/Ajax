<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataTablesController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('ajax-datatable-crud', [DataTablesController::class, 'index']);
Route::post('add-update-book', [DataTablesController::class, 'store']);
Route::post('edit-book', [DataTablesController::class, 'edit']);
Route::post('delete-book', [DataTablesController::class, 'destroy']);

//report
Route::get('/search',[DataTablesController::class,'bookIndex']);
Route::post('/book/search',[DataTablesController::class,'showBooks'])->name('book.search');