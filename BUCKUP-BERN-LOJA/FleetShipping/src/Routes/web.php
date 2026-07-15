<?php

use Illuminate\Support\Facades\Route;
use Webkul\FleetShipping\Http\Controllers\WebhookController;

/**
 * IMPORTANTE: esta rota precisa de estar isenta de verificação CSRF,
 * porque é chamada server-to-server pela Fleet, sem sessão de browser.
 * Ver docs/IMPLEMENTATION_GUIDE.md > "Excepção de CSRF para o webhook".
 */
Route::post('webhooks/fleet', [WebhookController::class, 'handle'])
    ->name('fleet.webhook')
    ->middleware(['throttle:60,1']);
