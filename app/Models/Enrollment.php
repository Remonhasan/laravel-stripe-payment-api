<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    protected $fillable = [
        'user_id',
        'course_id',
        'stripe_session_id',
        'payment_status',
    ];

    public static function getEnrollmentByStripeSessionId($stripeSessionId)
    {
        return self::where('stripe_session_id', $stripeSessionId)->first();
    }

    public static function isStripePaymentStatusPaid($stripeSessionId)
    {
        return self::where('stripe_session_id', $stripeSessionId)
            ->where('payment_status', 'paid')
            ->exists();
    }
}
