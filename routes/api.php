<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\StripeCheckoutController;
use App\Http\Controllers\StripeWebhookController;

// Check payment is successfull or not 
Route::post('/check-payment/{$stripeSessionId}', [StripeCheckoutController::class, 'isPaymentSuccess']);
// Checkout
Route::post('/checkout', [StripeCheckoutController::class, 'create']);
// Stripe webhook
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);

// Get Payment Info from stripe
Route::get('/payment-status', [StripeCheckoutController::class, 'getPaymentStatus']);
