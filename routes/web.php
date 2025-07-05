<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexContactController;
use App\Http\Controllers\ShowContactController;
use App\Http\Controllers\CreateContactFormController;
use App\Http\Controllers\CreateContactController;
use App\Http\Controllers\EditContactFormController;
use App\Http\Controllers\UpdateContactController;
use App\Http\Controllers\DeleteContactController;

Route::get('/', function () {
    return redirect()->route('contacts.index');
});

// Contacts CRUD routes
Route::get('/contacts', IndexContactController::class)->name('contacts.index');
Route::get('/contacts/create', CreateContactFormController::class)->name('contacts.create');
Route::post('/contacts', CreateContactController::class)->name('contacts.store');
Route::get('/contacts/{contact}', ShowContactController::class)->name('contacts.show');
Route::get('/contacts/{contact}/edit', EditContactFormController::class)->name('contacts.edit');
Route::put('/contacts/{contact}', UpdateContactController::class)->name('contacts.update');
Route::delete('/contacts/{contact}', DeleteContactController::class)->name('contacts.destroy');
