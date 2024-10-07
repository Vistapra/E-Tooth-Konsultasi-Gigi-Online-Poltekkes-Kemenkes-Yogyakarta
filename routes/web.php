<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FrontController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Chatify\MessagesController;

Route::get('/', [FrontController::class, 'index'])->name('front.index');
Route::get('/search', [FrontController::class, 'search'])->name('front.search');
Route::get('/details/{product:slug}', [FrontController::class, 'details'])->name('front.product.details');
Route::get('/category/{category}', [FrontController::class, 'category'])->name('front.product.category');

Route::get('/konsultasi', [FrontController::class, 'konsultasi'])->name('front.konsultasi');
Route::get('/riwayat', [FrontController::class, 'riwayat'])->name('front.riwayat');

require __DIR__ . '/auth.php';

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });

    Route::prefix('admin')->name('admin.')->middleware('role:owner')->group(function () {
        Route::resources([
            'products' => ProductController::class,
            'categories' => CategoryController::class,
            'doctor' => DoctorController::class,
        ]);
    });

    Route::prefix('chat')->group(function () {
        Route::get('/', [MessagesController::class, 'index'])->name('chatify');
        Route::post('/idInfo', [MessagesController::class, 'idFetchData']);
        Route::post('/sendMessage', [MessagesController::class, 'send'])->name('chatify.send.message');
        Route::post('/fetchMessages', [MessagesController::class, 'fetch'])->name('chatify.fetch.messages');
        Route::get('/download/{fileName}', [MessagesController::class, 'download'])->name(config('chatify.attachments.download_route_name'));
        Route::post('/chat/auth', [MessagesController::class, 'pusherAuth'])->name('chatify.pusher.auth');
        Route::post('/makeSeen', [MessagesController::class, 'seen'])->name('chatify.messages.seen');
        Route::get('/getContacts', [MessagesController::class, 'getContacts'])->name('chatify.contacts.get');
        Route::post('/updateContacts', [MessagesController::class, 'updateContactItem'])->name('chatify.contacts.update');
        Route::post('/favorite', [MessagesController::class, 'favorite'])->name('chatify.favorite');
        Route::post('/getFavorites', [MessagesController::class, 'getFavorites'])->name('chatify.favorites');
        Route::get('/search', [MessagesController::class, 'search'])->name('chatify.search');
        Route::post('/shared', [MessagesController::class, 'sharedPhotos'])->name('chatify.shared');
        Route::post('/deleteConversation', [MessagesController::class, 'deleteConversation'])->name('chatify.conversation.delete');
        Route::post('/deleteMessage', [MessagesController::class, 'deleteMessage'])->name('chatify.message.delete');
        Route::post('/updateSettings', [MessagesController::class, 'updateSettings'])->name('chatify.avatar.update');
        Route::post('/setActiveStatus', [MessagesController::class, 'setActiveStatus'])->name('chatify.activeStatus.set');
        Route::get('/group/{id}', [MessagesController::class, 'index'])->name('chatify.group');
        Route::get('/{id}', [MessagesController::class, 'index'])->name('chatify.user');

        Route::get('/chatify/unread-count', [MessagesController::class, 'getUnreadCount'])->name('chatify.unread.count');
        Route::post('/chat/mark-as-read', [MessagesController::class, 'markAsRead']);
        Route::get('/chat/user-status/{userId}', [MessagesController::class, 'getUserStatus']);
        Route::post('/chat/update-user-status', [MessagesController::class, 'updateUserStatus']);

        Route::post('/chatify/sendMessage', [MessagesController::class, 'send'])->name('chatify.send');
    });
});