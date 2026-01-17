<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Illuminate\Support\Facades\Cache;

use Illuminate\Http\Request;


class StripeWebhookController extends Controller
{

    // Call stripe webhook
    public function handle(Request $request)
    {
        $event = json_decode($request->getContent());

        if ($event->type !== 'checkout.session.completed') {
            return response('Ignored', 200);
        }

        $session = $event->data->object;

        $lock = Cache::lock(
            'stripe-webhook-' . $session->id,
            10 // seconds
        );

        if (! $lock->get()) {
            return response('Already processing', 200);
        }

        try {

            $stripePaymentInfo = $this->getPaymentStatus($session->id);

            $enrollment = Enrollment::getEnrollmentByStripeSessionId($session->id);

            if ($enrollment && !empty($stripePaymentInfo)) {
                $enrollment->update([
                    'payment_status' => $stripePaymentInfo['status']
                ]);
            }
        } finally {
            $lock->release();
        }

        return response('Payment Completed', 200);
    }


    // Get payment info from stripe
    public function getPaymentStatus($stripeSessionId)
    {

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::retrieve($stripeSessionId);

        return [
            'id' => $session->id,
            'status' => $session->payment_status,
            'amount_total' => $session->amount_total / 100,
            'currency' => $session->currency,
            'payment_id' => $session->payment_intent,
        ];
    }
}
