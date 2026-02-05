# Shwary PHP SDK

[![PHP Version](https://img.shields.io/badge/PHP-8.2%2B-blue)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)](https://phpstan.org/)

Official PHP SDK for the Shwary API - Mobile Money Payments for DRC, Kenya, and Uganda.

---

## 📋 Table of Contents

- [Introduction](#-introduction)
- [Requirements](#-requirements)
- [Installation](#-installation)
- [Configuration](#-configuration)
- [Quick Start Guide](#-quick-start-guide)
- [Detailed Usage](#-detailed-usage)
- [Handling Webhooks](#-handling-webhooks)
- [Sandbox Mode (Testing)](#-sandbox-mode-testing)
- [Error Handling](#-error-handling)
- [Laravel Integration](#-laravel-integration)
- [Complete Reference](#-complete-reference)
- [FAQ & Troubleshooting](#-faq--troubleshooting)
- [Contributing](#-contributing)

---

## 🎯 Introduction

### What is Shwary?

Shwary is a Mobile Money payment platform that enables merchants to accept payments from:
- 🇨🇩 **DRC (Democratic Republic of Congo)** - Payments in Congolese Francs (CDF)
- 🇰🇪 **Kenya** - Payments in Kenyan Shillings (KES)
- 🇺🇬 **Uganda** - Payments in Ugandan Shillings (UGX)

### What does this SDK do?

This SDK allows you to easily integrate Shwary into your PHP application to:
- ✅ Initiate Mobile Money payments
- ✅ Receive payment notifications (webhooks)
- ✅ Check transaction status
- ✅ Test your integrations in sandbox mode

### How does a Mobile Money payment work?

```
┌─────────────┐    1. Initiate payment   ┌─────────────┐
│  Your PHP   │ ───────────────────────► │   Shwary    │
│    App      │                          │    API      │
└─────────────┘                          └──────┬──────┘
                                                │
                                         2. Send request
                                                │
                                                ▼
                                         ┌─────────────┐
                                         │   Mobile    │
                                         │   Money     │
                                         │  Provider   │
                                         └──────┬──────┘
                                                │
                                         3. Customer confirms
                                            on their phone
                                                │
┌─────────────┐    4. Webhook notification      │
│  Your PHP   │ ◄───────────────────────────────┘
│    App      │    (payment confirmed)
└─────────────┘
```

---

## 📦 Requirements

Before you begin, make sure you have:

### Technical Requirements
- **PHP 8.2 or higher** - [Download PHP](https://php.net/downloads)
- **Composer** - [Install Composer](https://getcomposer.org/download/)
- **JSON extension** - Usually included by default

### Check your PHP version

```bash
php -v
# Should display: PHP 8.2.x or higher
```

### Shwary Account
- An active Shwary merchant account
- Your **Merchant ID** (unique identifier)
- Your **Merchant Key** (secret key)

> 💡 **Where to find your credentials?**  
> Log in to your [Shwary Dashboard](https://app.shwary.com/settings) → Settings → API

---

## 🔧 Installation

### Step 1: Install the SDK via Composer

Open your terminal in your project folder and run:

```bash
composer require shwary/php-sdk
```

### Step 2: Verify the installation

Create a test file `test-shwary.php`:

```php
<?php

require_once 'vendor/autoload.php';

use Shwary\Enums\Country;

// If this code runs without error, the installation is successful
echo "✅ Shwary SDK installed successfully!\n";
echo "Supported countries: " . implode(', ', array_column(Country::cases(), 'name')) . "\n";
```

Run it:

```bash
php test-shwary.php
```

---

## ⚙️ Configuration

There are **3 ways** to configure the SDK. Choose the one that suits your needs.

### Method 1: Environment Variables (Recommended ⭐)

**Why is this recommended?** Your credentials are not in the code, which is more secure.

#### Step A: Create a `.env` file at the root of your project

```env
# Shwary credentials
SHWARY_MERCHANT_ID=your-merchant-id-here
SHWARY_MERCHANT_KEY=your-merchant-key-here

# Mode (true for testing, false for production)
SHWARY_SANDBOX=false

# Optional: timeout in seconds (default: 30)
SHWARY_TIMEOUT=30
```

#### Step B: Load the variables (if you're not using a framework)

```php
<?php

require_once 'vendor/autoload.php';

// If using vlucas/phpdotenv (composer require vlucas/phpdotenv)
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Or load manually
// putenv('SHWARY_MERCHANT_ID=your-merchant-id');
// putenv('SHWARY_MERCHANT_KEY=your-merchant-key');
```

#### Step C: Initialize the SDK

```php
use Shwary\Shwary;

// The SDK automatically reads environment variables
Shwary::initFromEnvironment();

// Ready! You can now use Shwary::
```

---

### Method 2: Array Configuration

Useful if you store your configurations in a PHP file or database.

```php
<?php

require_once 'vendor/autoload.php';

use Shwary\Shwary;

// Define your configuration
$config = [
    'merchant_id'  => 'your-merchant-id-here',
    'merchant_key' => 'your-merchant-key-here',
    'sandbox'      => false,  // true for testing
    'timeout'      => 30,     // optional
];

// Initialize the SDK
Shwary::initFromArray($config);

// Ready!
```

---

### Method 3: Manual Configuration (Full Control)

For advanced developers who want full control.

```php
<?php

require_once 'vendor/autoload.php';

use Shwary\Config;
use Shwary\ShwaryClient;

// Create a configuration object
$config = new Config(
    merchantId: 'your-merchant-id-here',
    merchantKey: 'your-merchant-key-here',
    baseUrl: 'https://api.shwary.com',  // optional
    timeout: 30,                         // optional
    sandbox: false                       // true for testing
);

// Create the client
$client = new ShwaryClient($config);

// Use $client->payDRC(), $client->payKenya(), etc.
```

---

## 🚀 Quick Start Guide

### Your first payment in 5 minutes

Here's a complete, working example:

```php
<?php
/**
 * My first Shwary payment
 * 
 * This script initiates a Mobile Money payment of 5000 CDF 
 * to a phone number in DRC.
 */

require_once 'vendor/autoload.php';

use Shwary\Shwary;
use Shwary\Exceptions\ShwaryException;

// 1. Configure the SDK
Shwary::initFromArray([
    'merchant_id'  => 'your-merchant-id',
    'merchant_key' => 'your-merchant-key',
    'sandbox'      => true,  // Test mode to start
]);

// 2. Initiate a payment
try {
    $transaction = Shwary::payDRC(
        amount: 5000,                    // Amount in CDF
        phone: '+243812345678',          // Customer's phone number
        callbackUrl: 'https://your-site.com/webhook'  // Optional
    );

    // 3. Display the result
    echo "✅ Payment initiated successfully!\n";
    echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    echo "Transaction ID : {$transaction->id}\n";
    echo "Amount         : {$transaction->amount} {$transaction->currency}\n";
    echo "Status         : {$transaction->status->value}\n";
    echo "Phone          : {$transaction->recipientPhoneNumber}\n";
    echo "Reference      : {$transaction->referenceId}\n";
    echo "Sandbox Mode   : " . ($transaction->isSandbox ? 'Yes' : 'No') . "\n";

} catch (ShwaryException $e) {
    // 4. Handle errors
    echo "❌ Error: {$e->getMessage()}\n";
    echo "Code: {$e->getCode()}\n";
}
```

**Expected output:**

```
✅ Payment initiated successfully!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Transaction ID : 550e8400-e29b-41d4-a716-446655440000
Amount         : 5000 CDF
Status         : pending
Phone          : +243812345678
Reference      : ref_abc123xyz
Sandbox Mode   : Yes
```

---

## 📚 Detailed Usage

### Payments by Country

#### 🇨🇩 Payment in DRC (Democratic Republic of Congo)

```php
use Shwary\Shwary;

// Simplified method
$transaction = Shwary::payDRC(
    amount: 5000,                // Minimum: 2901 CDF
    phone: '+243812345678',      // Must start with +243
    callbackUrl: 'https://...'   // Optional but recommended
);

// Check status
if ($transaction->isPending()) {
    echo "Waiting for customer confirmation...";
}
```

> ⚠️ **Important for DRC:**  
> - Minimum amount is **2901 CDF**
> - Phone number must start with **+243**

---

#### 🇰🇪 Payment in Kenya

```php
use Shwary\Shwary;

$transaction = Shwary::payKenya(
    amount: 1000,                // Amount in KES
    phone: '+254712345678',      // Must start with +254
    callbackUrl: 'https://...'
);
```

---

#### 🇺🇬 Payment in Uganda

```php
use Shwary\Shwary;

$transaction = Shwary::payUganda(
    amount: 5000,                // Amount in UGX
    phone: '+256712345678',      // Must start with +256
    callbackUrl: 'https://...'
);
```

---

### Generic Method with Country

If you want to handle multiple countries dynamically:

```php
use Shwary\Shwary;
use Shwary\Enums\Country;

// Determine the country (from a form, database, etc.)
$countryCode = 'DRC'; // or 'KE', 'UG'
$country = Country::from($countryCode);

// Generic payment
$transaction = Shwary::pay(
    amount: 5000,
    phone: '+243812345678',
    country: $country,
    callbackUrl: 'https://your-site.com/webhook'
);
```

---

### Using PaymentRequest (Advanced Control)

For full control over the payment request:

```php
use Shwary\Shwary;
use Shwary\DTOs\PaymentRequest;
use Shwary\Enums\Country;

// Create a payment request
$request = new PaymentRequest(
    amount: 5000,
    clientPhoneNumber: '+243812345678',
    country: Country::DRC,
    callbackUrl: 'https://your-site.com/webhook'
);

// Or use the factory method
$request = PaymentRequest::create(
    amount: 5000,
    phone: '+243812345678',
    country: Country::DRC,
    callbackUrl: 'https://your-site.com/webhook'
);

// Send the request
$transaction = Shwary::createPayment($request);
```

---

## 🔔 Handling Webhooks

### What is a webhook?

A webhook is an automatic notification sent by Shwary to your server when a payment status changes (confirmed, failed, etc.).

### Webhook Configuration

#### Step 1: Create an endpoint on your server

```php
<?php
/**
 * File: webhook.php
 * URL: https://your-site.com/webhook.php
 * 
 * This file receives notifications from Shwary
 */

require_once 'vendor/autoload.php';

use Shwary\Shwary;
use Shwary\DTOs\Transaction;
use Shwary\Exceptions\ShwaryException;

// Initialize the SDK
Shwary::initFromEnvironment();

// Set the response header
header('Content-Type: application/json');

try {
    // Automatically parse the webhook
    $transaction = Shwary::webhook()->parseFromGlobals();
    
    // Process based on status
    processTransaction($transaction);
    
    // Respond to Shwary with success
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'Webhook processed successfully'
    ]);

} catch (ShwaryException $e) {
    // On error, respond with an error code
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

/**
 * Process the transaction based on its status
 */
function processTransaction(Transaction $transaction): void
{
    // Log the transaction (recommended)
    error_log("Webhook received - Transaction: {$transaction->id}, Status: {$transaction->status->value}");

    switch (true) {
        case $transaction->isCompleted():
            // ✅ Payment successful!
            handleSuccessfulPayment($transaction);
            break;

        case $transaction->isFailed():
            // ❌ Payment failed
            handleFailedPayment($transaction);
            break;

        case $transaction->isPending():
            // ⏳ Still pending
            handlePendingPayment($transaction);
            break;
    }
}

function handleSuccessfulPayment(Transaction $transaction): void
{
    // Example: Update your database
    // UPDATE orders SET status = 'paid' WHERE reference = ?
    
    error_log("✅ Payment confirmed: {$transaction->amount} {$transaction->currency}");
    
    // Example: Send a confirmation email
    // mail($customer_email, "Payment confirmed", "...");
}

function handleFailedPayment(Transaction $transaction): void
{
    error_log("❌ Payment failed: {$transaction->failureReason}");
    
    // Example: Notify the customer
    // Example: Offer another payment method
}

function handlePendingPayment(Transaction $transaction): void
{
    error_log("⏳ Payment pending: {$transaction->id}");
}
```

#### Step 2: Register the webhook URL

Configure the URL `https://your-site.com/webhook.php` in your Shwary Dashboard.

---

### Parse a webhook manually

If you retrieve the payload differently (e.g., framework):

```php
// From a JSON string
$jsonPayload = file_get_contents('php://input');
$transaction = Shwary::parseWebhook($jsonPayload);

// Or via the handler
$transaction = Shwary::webhook()->parsePayload($jsonPayload);
```

---

## 🧪 Sandbox Mode (Testing)

Sandbox mode allows you to test without making real payments.

### Enable sandbox mode

```php
// Via configuration
Shwary::initFromArray([
    'merchant_id'  => 'your-merchant-id',
    'merchant_key' => 'your-merchant-key',
    'sandbox'      => true,  // ← Enable test mode
]);
```

### Make a sandbox payment

```php
use Shwary\Shwary;
use Shwary\Enums\Country;

// Method 1: Via sandboxPay
$transaction = Shwary::sandboxPay(
    amount: 5000,
    phone: '+243812345678',
    country: Country::DRC
);

// Method 2: Via createSandboxPayment
$request = PaymentRequest::create(5000, '+243812345678', Country::DRC);
$transaction = Shwary::createSandboxPayment($request);

// Sandbox transactions are immediately "completed"
var_dump($transaction->isSandbox);     // true
var_dump($transaction->isCompleted()); // true
```

### Sandbox vs Production differences

| Aspect | Sandbox | Production |
|--------|---------|------------|
| Real payment | ❌ No | ✅ Yes |
| Status | Immediately `completed` | `pending` then `completed/failed` |
| Webhooks | Simulated | Real |
| Money charged | ❌ No | ✅ Yes |

---

## ⚠️ Error Handling

### Exception Types

The SDK defines several error types to help you handle them:

```php
use Shwary\Exceptions\ShwaryException;        // Base exception
use Shwary\Exceptions\AuthenticationException; // Authentication error (401)
use Shwary\Exceptions\ValidationException;     // Validation error
use Shwary\Exceptions\ApiException;            // Other API errors
```

### Complete Error Handling Example

```php
<?php

use Shwary\Shwary;
use Shwary\Exceptions\AuthenticationException;
use Shwary\Exceptions\ValidationException;
use Shwary\Exceptions\ApiException;
use Shwary\Exceptions\ShwaryException;

try {
    $transaction = Shwary::payDRC(5000, '+243812345678');
    
    echo "✅ Payment initiated: {$transaction->id}";

} catch (AuthenticationException $e) {
    // ❌ Error 401: Invalid credentials
    echo "Authentication error: {$e->getMessage()}\n";
    echo "Check your MERCHANT_ID and MERCHANT_KEY\n";

} catch (ValidationException $e) {
    // ❌ Validation error (amount, phone, etc.)
    echo "Validation error: {$e->getMessage()}\n";
    
    // Get the details
    $details = $e->getContext();
    print_r($details);
    
    // Examples of validation errors:
    // - "Amount must be greater than 2900 CDF for DRC"
    // - "Phone number must start with +243 for DRC"
    // - "Callback URL must use HTTPS"

} catch (ApiException $e) {
    // ❌ Other API errors
    $code = $e->getCode();
    
    switch ($code) {
        case 404:
            echo "Customer not found in the Mobile Money system\n";
            break;
            
        case 502:
            echo "Temporary provider error. Try again later.\n";
            break;
            
        case 500:
            echo "Shwary server error. Contact support.\n";
            break;
            
        default:
            echo "API error ({$code}): {$e->getMessage()}\n";
    }
    
    // Get the original HTTP response if available
    $response = $e->getResponse();

} catch (ShwaryException $e) {
    // ❌ Any other Shwary error
    echo "Shwary error: {$e->getMessage()}\n";

} catch (\Exception $e) {
    // ❌ Unexpected error (network, etc.)
    echo "Unexpected error: {$e->getMessage()}\n";
}
```

### Common Error Codes

| Code | Meaning | Recommended Action |
|------|---------|-------------------|
| 400 | Invalid request | Check parameters |
| 401 | Unauthenticated | Verify merchant_id and merchant_key |
| 404 | Resource not found | The number is not registered |
| 422 | Validation failed | Correct the sent data |
| 500 | Server error | Retry or contact support |
| 502 | Gateway error | Temporary issue, retry |

---

## 🎨 Laravel Integration

### Installation

```bash
composer require shwary/php-sdk
```

### Configuration

#### 1. Create the configuration file

```php
<?php
// config/shwary.php

return [
    /*
    |--------------------------------------------------------------------------
    | Shwary Credentials
    |--------------------------------------------------------------------------
    |
    | Your Shwary merchant credentials. Keep them secret!
    |
    */
    'merchant_id' => env('SHWARY_MERCHANT_ID'),
    'merchant_key' => env('SHWARY_MERCHANT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Sandbox Mode
    |--------------------------------------------------------------------------
    |
    | In sandbox mode, no real payment is made.
    | Ideal for testing and development.
    |
    */
    'sandbox' => env('SHWARY_SANDBOX', false),

    /*
    |--------------------------------------------------------------------------
    | Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum timeout for API requests (in seconds).
    |
    */
    'timeout' => env('SHWARY_TIMEOUT', 30),
];
```

#### 2. Add environment variables

```env
# .env
SHWARY_MERCHANT_ID=your-merchant-id
SHWARY_MERCHANT_KEY=your-merchant-key
SHWARY_SANDBOX=true
```

#### 3. Create a Service Provider

```php
<?php
// app/Providers/ShwaryServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Shwary\Config;
use Shwary\ShwaryClient;

class ShwaryServiceProvider extends ServiceProvider
{
    /**
     * Register the Shwary client as a singleton.
     */
    public function register(): void
    {
        $this->app->singleton(ShwaryClient::class, function ($app) {
            return new ShwaryClient(
                Config::fromArray(config('shwary'))
            );
        });

        // Convenient alias
        $this->app->alias(ShwaryClient::class, 'shwary');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/shwary.php' => config_path('shwary.php'),
        ], 'shwary-config');
    }
}
```

#### 4. Register the provider

```php
// bootstrap/providers.php (Laravel 11+)
return [
    // ...
    App\Providers\ShwaryServiceProvider::class,
];

// Or config/app.php (Laravel 10 and earlier)
'providers' => [
    // ...
    App\Providers\ShwaryServiceProvider::class,
],
```

### Using in a Controller

```php
<?php
// app/Http/Controllers/PaymentController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Shwary\ShwaryClient;
use Shwary\Enums\Country;
use Shwary\Exceptions\ShwaryException;

class PaymentController extends Controller
{
    /**
     * Inject the Shwary client via constructor.
     */
    public function __construct(
        private ShwaryClient $shwary
    ) {}

    /**
     * Initiate a Mobile Money payment.
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        // Validate data
        $validated = $request->validate([
            'amount' => 'required|integer|min:1',
            'phone' => 'required|string',
            'country' => 'required|in:DRC,KE,UG',
        ]);

        try {
            // Determine the country
            $country = Country::from($validated['country']);

            // Initiate the payment
            $transaction = $this->shwary->pay(
                amount: $validated['amount'],
                phone: $validated['phone'],
                country: $country,
                callbackUrl: route('webhooks.shwary')
            );

            // Save to database (example)
            // Payment::create([
            //     'transaction_id' => $transaction->id,
            //     'reference' => $transaction->referenceId,
            //     'amount' => $transaction->amount,
            //     'status' => $transaction->status->value,
            // ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment initiated successfully',
                'data' => [
                    'transaction_id' => $transaction->id,
                    'reference' => $transaction->referenceId,
                    'status' => $transaction->status->value,
                    'amount' => $transaction->amount,
                    'currency' => $transaction->currency,
                ],
            ]);

        } catch (ShwaryException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'code' => $e->getCode(),
            ], 422);
        }
    }

    /**
     * Shortcut for DRC payment.
     */
    public function payDRC(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:2901',
            'phone' => ['required', 'string', 'regex:/^\+243/'],
        ]);

        try {
            $transaction = $this->shwary->payDRC(
                amount: $validated['amount'],
                phone: $validated['phone'],
                callbackUrl: route('webhooks.shwary')
            );

            return response()->json([
                'success' => true,
                'transaction' => $transaction->toArray(),
            ]);

        } catch (ShwaryException $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 422);
        }
    }
}
```

### Webhook Controller

```php
<?php
// app/Http/Controllers/WebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Shwary\ShwaryClient;
use Shwary\DTOs\Transaction;
use Shwary\Exceptions\ShwaryException;

class WebhookController extends Controller
{
    public function __construct(
        private ShwaryClient $shwary
    ) {}

    /**
     * Receive webhooks from Shwary.
     */
    public function handleShwary(Request $request): JsonResponse
    {
        try {
            // Parse the webhook
            $transaction = $this->shwary->parseWebhook(
                $request->getContent()
            );

            // Log for debugging
            Log::info('Shwary webhook received', [
                'transaction_id' => $transaction->id,
                'status' => $transaction->status->value,
                'amount' => $transaction->amount,
            ]);

            // Process based on status
            $this->processTransaction($transaction);

            return response()->json(['success' => true]);

        } catch (ShwaryException $e) {
            Log::error('Shwary webhook error', [
                'error' => $e->getMessage(),
                'payload' => $request->getContent(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process the transaction based on its status.
     */
    private function processTransaction(Transaction $transaction): void
    {
        // Find the payment in database
        // $payment = Payment::where('transaction_id', $transaction->id)->first();

        if ($transaction->isCompleted()) {
            // Payment successful
            Log::info("✅ Payment confirmed: {$transaction->id}");
            
            // $payment->update(['status' => 'completed']);
            // event(new PaymentCompleted($payment));
            
        } elseif ($transaction->isFailed()) {
            // Payment failed
            Log::warning("❌ Payment failed: {$transaction->id}", [
                'reason' => $transaction->failureReason,
            ]);
            
            // $payment->update([
            //     'status' => 'failed',
            //     'failure_reason' => $transaction->failureReason,
            // ]);
        }
    }
}
```

### Routes

```php
<?php
// routes/web.php or routes/api.php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\WebhookController;

// Payment routes
Route::post('/payments/initiate', [PaymentController::class, 'initiatePayment']);
Route::post('/payments/drc', [PaymentController::class, 'payDRC']);

// Webhook route (exclude from CSRF if needed)
Route::post('/webhooks/shwary', [WebhookController::class, 'handleShwary'])
    ->name('webhooks.shwary')
    ->withoutMiddleware(['csrf']); // Important for webhooks
```

---

## 📖 Complete Reference

### Supported Countries

| Constant | Country | Currency | Dial Code | Minimum Amount |
|----------|---------|----------|-----------|----------------|
| `Country::DRC` | DR Congo | CDF | +243 | > 2900 CDF |
| `Country::KENYA` | Kenya | KES | +254 | > 0 KES |
| `Country::UGANDA` | Uganda | UGX | +256 | > 0 UGX |

### Transaction Statuses

| Constant | Value | Description |
|----------|-------|-------------|
| `TransactionStatus::PENDING` | `pending` | Waiting for confirmation |
| `TransactionStatus::COMPLETED` | `completed` | Payment successful |
| `TransactionStatus::FAILED` | `failed` | Payment failed |

### Transaction Properties

```php
$transaction->id;                   // string - Transaction UUID
$transaction->userId;               // string - Merchant UUID
$transaction->amount;               // int - Amount
$transaction->currency;             // string - Currency (CDF, KES, UGX)
$transaction->type;                 // string - Type (deposit)
$transaction->status;               // TransactionStatus - Status
$transaction->recipientPhoneNumber; // string - Customer's phone number
$transaction->referenceId;          // string - Unique reference
$transaction->metadata;             // ?array - Metadata
$transaction->failureReason;        // ?string - Failure reason
$transaction->completedAt;          // ?DateTimeImmutable - Completion date
$transaction->createdAt;            // DateTimeImmutable - Creation date
$transaction->updatedAt;            // DateTimeImmutable - Update date
$transaction->isSandbox;            // bool - Test mode
$transaction->pretiumTransactionId; // ?string - Pretium ID
$transaction->error;                // ?string - Error message
```

### Transaction Methods

```php
$transaction->isPending();    // bool - Check if pending
$transaction->isCompleted();  // bool - Check if successful
$transaction->isFailed();     // bool - Check if failed
$transaction->isTerminal();   // bool - Check if final state
$transaction->toArray();      // array - Convert to array
$transaction->jsonSerialize(); // array - For json_encode()
```

### Shwary Facade Methods

```php
// Initialization
Shwary::init(Config $config);
Shwary::initFromEnvironment();
Shwary::initFromArray(array $config);

// Payments
Shwary::pay(int $amount, string $phone, Country $country, ?string $callbackUrl = null): Transaction;
Shwary::payDRC(int $amount, string $phone, ?string $callbackUrl = null): Transaction;
Shwary::payKenya(int $amount, string $phone, ?string $callbackUrl = null): Transaction;
Shwary::payUganda(int $amount, string $phone, ?string $callbackUrl = null): Transaction;
Shwary::createPayment(PaymentRequest $request): Transaction;

// Sandbox
Shwary::sandboxPay(int $amount, string $phone, Country $country, ?string $callbackUrl = null): Transaction;
Shwary::createSandboxPayment(PaymentRequest $request): Transaction;

// Webhooks
Shwary::parseWebhook(string $payload): Transaction;
Shwary::webhook(): WebhookHandler;

// Utilities
Shwary::client(): ShwaryClient;
Shwary::isSandbox(): bool;
Shwary::getConfig(): Config;
Shwary::reset(): void;
```

---

## ❓ FAQ & Troubleshooting

### Frequently Asked Questions

#### Q: How can I test without making real payments?
**A:** Use sandbox mode by setting `sandbox: true` in your configuration.

#### Q: Why does my payment stay in "pending"?
**A:** The `pending` status means the customer hasn't confirmed on their phone yet. Wait for the confirmation webhook.

#### Q: How do I know if a payment was successful?
**A:** Check with `$transaction->isCompleted()` or wait for the webhook.

#### Q: What's the difference between `pay()` and `payDRC()`?
**A:** `payDRC()` is a shortcut for `pay(..., Country::DRC)`. They do the same thing.

### Common Errors

#### "Merchant ID is required"
```php
// ❌ Wrong
Shwary::initFromArray([]);

// ✅ Correct
Shwary::initFromArray([
    'merchant_id' => 'your-id',
    'merchant_key' => 'your-key',
]);
```

#### "Amount must be greater than 2900 CDF for DRC"
```php
// ❌ Wrong
Shwary::payDRC(amount: 1000, phone: '+243...');

// ✅ Correct (minimum 2901 CDF)
Shwary::payDRC(amount: 5000, phone: '+243...');
```

#### "Phone number must start with +243 for DRC"
```php
// ❌ Wrong
Shwary::payDRC(amount: 5000, phone: '0812345678');

// ✅ Correct
Shwary::payDRC(amount: 5000, phone: '+243812345678');
```

#### "Callback URL must use HTTPS"
```php
// ❌ Wrong
Shwary::payDRC(5000, '+243...', 'http://example.com/webhook');

// ✅ Correct
Shwary::payDRC(5000, '+243...', 'https://example.com/webhook');
```

---

## 🧪 Tests

### Run tests

```bash
# All tests
composer test

# With code coverage
composer test -- --coverage

# Specific tests
./vendor/bin/pest tests/Unit/ShwaryClientTest.php
```

### Statistics

- **113 tests**
- **278 assertions**
- **PHPStan level max**

---

## 🤝 Contributing

Contributions are welcome! Here's how to proceed:

1. **Fork** the repository
2. Create a **branch** for your feature (`git checkout -b feature/my-feature`)
3. **Commit** your changes (`git commit -m 'Add my feature'`)
4. **Push** to the branch (`git push origin feature/my-feature`)
5. Open a **Pull Request**

### Code Standards

- PSR-12 for code style
- PHPStan level max
- Pest tests for any new feature

---

## 📄 License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

---

## 📞 Support

- 📧 Email: tresorkasendat@gmail.com
- 📚 Documentation: [docs.shwary.com](https://github.com/Tresor-Kasenda/shwary-php-sdk)
- 🐛 Issues: [GitHub Issues](https://github.com/Tresor-Kasenda/shwary-php-sdk/issues)

---

Made with ❤️ by the Shwary Team
