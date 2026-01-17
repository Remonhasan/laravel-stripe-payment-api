# Stripe Payment Integration for Laravel

A Laravel-based backend application for handling course enrollment payments using Stripe Checkout. This project demonstrates a complete payment flow with webhook handling, enrollment tracking, and payment status management.

## Features

- 🎓 **Course Management**: Create and manage courses with pricing
- 💳 **Stripe Checkout Integration**: Seamless payment processing using Stripe Checkout Sessions
- 📝 **Enrollment Tracking**: Track user enrollments and payment status
- 🔔 **Webhook Handling**: Automatic payment status updates via Stripe webhooks
- 🔒 **Race Condition Prevention**: Cache-based locking to prevent duplicate webhook processing
- ✅ **Payment Status Verification**: API endpoints to check payment completion status

## Technology Stack

- **Framework**: Laravel 12.x
- **PHP**: 8.2+
- **Payment Gateway**: Stripe (stripe/stripe-php ^19.1)
- **Database**: SQLite (default, can be configured for MySQL/PostgreSQL)

## Requirements

- PHP >= 8.2
- Composer
- Stripe Account (API keys)

**Optional** (only if you need frontend asset compilation):
- Node.js & NPM

## Installation

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/stripe-payment.git
   cd stripe-payment
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Configure Stripe credentials**
   
   Add your Stripe API keys to `.env`:
   ```env
   STRIPE_KEY=your_stripe_publishable_key
   STRIPE_SECRET=your_stripe_secret_key
   FRONTEND_URL=http://localhost:3000
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

**Optional** - If you need frontend assets (CSS/JS):
```bash
npm install
npm run build
```

## Quick Start

You can use the provided Composer scripts for quick setup:

```bash
# Complete setup (install dependencies, generate key, migrate, build assets)
composer setup

# Development server (runs server, queue, logs, and vite concurrently)
# Note: This requires npm to be installed
composer dev
```

**Note**: The `composer setup` and `composer dev` scripts include npm commands. For API-only usage, you can skip npm entirely and run:
```bash
composer install
php artisan key:generate
php artisan migrate
php artisan serve
```

## API Endpoints

### 1. Create Checkout Session
**POST** `/api/checkout`

Creates a Stripe Checkout Session for course enrollment.

**Request Body:**
```json
{
  "course_id": 1
}
```

**Response:**
```json
{
  "url": "https://checkout.stripe.com/pay/cs_test_..."
}
```

### 2. Check Payment Status
**POST** `/api/check-payment/{stripeSessionId}`

Checks if payment for a session is completed.

**Response:**
```json
true
```

### 3. Get Payment Status (Detailed)
**GET** `/api/payment-status?session_id={stripeSessionId}`

Retrieves detailed payment information from Stripe.

**Response:**
```json
{
  "id": "cs_test_...",
  "status": "paid",
  "amount_total": 99.99,
  "currency": "usd",
  "payment_id": "pi_..."
}
```

### 4. Stripe Webhook
**POST** `/api/stripe/webhook`

Handles Stripe webhook events (automatically called by Stripe).

**Note**: Configure this endpoint in your Stripe Dashboard webhook settings.

## Database Schema

### Courses
- `id` (primary key)
- `title` (string)
- `price` (float)
- `timestamps`

### Enrollments
- `id` (primary key)
- `user_id` (foreign key)
- `course_id` (foreign key)
- `stripe_session_id` (string, nullable)
- `payment_status` (string, default: 'pending')
- `timestamps`

## Webhook Configuration

To set up Stripe webhooks:

1. Go to [Stripe Dashboard](https://dashboard.stripe.com/webhooks)
2. Click "Add endpoint"
3. Enter your webhook URL: `https://yourdomain.com/api/stripe/webhook`
4. Select events to listen for: `checkout.session.completed`
5. Copy the webhook signing secret (optional, for verification)

## Project Structure

```
app/
├── Http/
│   └── Controllers/
│       ├── StripeCheckoutController.php  # Handles checkout creation and status checks
│       └── StripeWebhookController.php    # Processes Stripe webhook events
├── Models/
│   ├── Course.php                         # Course model
│   └── Enrollment.php                     # Enrollment model with payment tracking
└── helpers.php                            # Helper functions

routes/
├── api.php                                # API routes for Stripe integration
└── web.php                                # Web routes

database/
└── migrations/
    ├── create_courses_table.php
    └── create_enrollments_table.php
```

## Key Features Explained

### Race Condition Prevention
The webhook handler uses Laravel's cache locking mechanism to prevent duplicate processing when multiple webhook events arrive simultaneously:

```php
$lock = Cache::lock('stripe-webhook-' . $session->id, 10);
```

### Payment Flow
1. User initiates checkout → `POST /api/checkout`
2. Stripe Checkout Session is created
3. Enrollment record is created with `payment_status: 'pending'`
4. User completes payment on Stripe
5. Stripe sends webhook event → `POST /api/stripe/webhook`
6. Payment status is updated to `'paid'` in enrollment record

## Development

### Running Tests
```bash
composer test
# or
php artisan test
```

### Code Formatting
```bash
./vendor/bin/pint
```

## Environment Variables

Required environment variables in `.env`:

```env
APP_NAME="Stripe Payment"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

STRIPE_KEY=pk_test_...
STRIPE_SECRET=sk_test_...
FRONTEND_URL=http://localhost:3000
```

## Security Considerations

- Never commit `.env` file with real API keys
- Use environment-specific keys (test vs live)
- Implement webhook signature verification in production
- Add proper authentication/authorization for API endpoints
- Validate and sanitize all user inputs

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Support

For issues and questions:
- Check [Stripe Documentation](https://stripe.com/docs)
- Review [Laravel Documentation](https://laravel.com/docs)
- Open an issue in this repository

## Acknowledgments

- Built with [Laravel](https://laravel.com)
- Payment processing by [Stripe](https://stripe.com)
