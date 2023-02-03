<?php

use DefStudio\Telegraph\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::post('/telegraph/{token}/webhook', [config('telegraph.route_controller'), 'handle'])->name('telegraph.webhook');
