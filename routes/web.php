<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\ConversationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// RUTAS DEL PANEL DE ADMIN
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de Conversaciones
    Route::get('/conversations', [ConversationController::class, 'index'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show'])->name('conversations.show');

    // Ruta para enviar respuesta desde el panel
    Route::post('/conversations/{conversation}/send', [ConversationController::class, 'sendMessageToContact'])
        ->name('conversations.send');
});

require __DIR__.'/auth.php';