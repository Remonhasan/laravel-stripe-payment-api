<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use Stripe\Stripe;
use Stripe\Checkout\Session;

use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    // Create checkout
    public function create(Request $request)
    {
        $course = Course::findOrFail($request->course_id);

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'mode' => 'payment',
            'line_items' => [[
                'price_data' => [
                    'currency' => 'usd',
                    'unit_amount' => $course->price * 100,
                    'product_data' => [
                        'name' => $course->title,
                    ],
                ],
                'quantity' => 1,
            ]],
            'success_url' => config('app.frontend_url') . '/checkout/success?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => config('app.frontend_url') . '/checkout/cancel',
            'metadata' => [
                'user_id' => 1,
                'course_id' => $course->id,
            ],
        ]);

        Enrollment::create([
            'user_id' => 1,
            'course_id' => $course->id,
            'stripe_session_id' => $session->id,
        ]);

        return response()->json([
            'url' => $session->url,
        ]);
    }

    public function isPaymentSuccess($stripeSessionId)
    {
        $isStatusPaid = Enrollment::isStripePaymentStatusPaid($stripeSessionId);
        return $isStatusPaid ? true : false;
    }
}
