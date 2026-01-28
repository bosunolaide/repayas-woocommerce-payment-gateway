# Repayas WooCommerce Payment Gateway

A production-ready WooCommerce payment gateway integration for
**Repayas**, featuring:

✅ Secure API initiation\
✅ Automatic redirect handling\
✅ Webhook / IPN processing\
✅ Custom `Invoiced` order status\
✅ WooCommerce logging\
✅ Test & Live modes\
✅ Robust error handling

------------------------------------------------------------------------

## 🚀 Features

-   **Repayas Checkout Integration**\
    Redirect customers to Repayas to complete payment securely.

-   **Automatic Order Updates via IPN/Webhook**\
    Orders are marked as paid automatically when Repayas sends a
    callback.

-   **Custom Order Status -- `Invoiced`**\
    Successful payments move orders into a dedicated `Invoiced` state
    (can be changed to `Completed` or `Processing`).

-   **Flexible API Response Parsing**\
    Works even if Repayas returns:

    -   `redirect_url`
    -   `payment_url`
    -   `payment_link`
    -   nested fields like `data.redirect_url`

-   **Built-in WooCommerce Logging**\
    Debug API requests and webhook calls from:

        WooCommerce → Status → Logs

-   **Secure by Default**\
    TLS verification enabled and sanitised webhook handling.

-   **Test Mode Support**\
    Easily switch between sandbox and live Repayas endpoints.

------------------------------------------------------------------------

## 📦 Installation

### Option 1 -- Upload ZIP

1.  Download the plugin ZIP.
2.  Go to **WordPress Admin → Plugins → Add New → Upload Plugin**.
3.  Upload the ZIP and activate it.

### Option 2 -- Manual Install

1.  Clone or download this repository.

2.  Upload the folder to:

        wp-content/plugins/

3.  Activate **Repayas WooCommerce Payment Gateway** from the admin
    panel.

------------------------------------------------------------------------

## ⚙️ Configuration

Navigate to:

    WooCommerce → Settings → Payments → Repayas

Fill in:

-   ✅ Enable Gateway
-   🔑 Public Key
-   🔐 Secret Key
-   🧪 Test Mode (optional)
-   🔔 IPN URL

------------------------------------------------------------------------

## 🔔 Webhook / IPN Setup (IMPORTANT)

In your Repayas dashboard, set the webhook / IPN endpoint to:

    https://your-site.com/?wc-api=repayas

This is required for WooCommerce to automatically update order statuses
after payment.

------------------------------------------------------------------------

## 🔄 Order Status Flow

By default:

  Event                            WooCommerce Status
  -------------------------------- --------------------
  Order placed                     Pending
  Customer redirected to Repayas   Pending
  Repayas confirms payment         Invoiced
  Admin fulfils order              Completed

👉 Want a different flow?

Edit in:

    includes/class-wc-payment-gateway-repayas.php

Change:

``` php
$status = 'invoiced';
```

to:

``` php
$status = 'completed';
```

or:

``` php
$status = 'processing';
```

------------------------------------------------------------------------

## 🧪 Testing

1.  Enable **Test Mode** in the gateway settings.
2.  Use Repayas sandbox keys.
3.  Place an order.
4.  Complete checkout.
5.  Confirm:
    -   Order status changes automatically.

    -   Logs appear under:

            WooCommerce → Status → Logs → repayas / repayas-ipn

------------------------------------------------------------------------

## 📄 Logs & Debugging

WooCommerce logs all:

-   API initiation requests
-   API responses
-   IPN payloads

Access logs via:

    WooCommerce → Status → Logs

Look for:

-   `repayas`
-   `repayas-ipn`

These are invaluable when debugging API issues.

------------------------------------------------------------------------

## 🛡 Security Notes

-   TLS certificate verification is enabled.
-   Payloads are sanitised.
-   Amount mismatches are logged.
-   Orders are validated before being updated.

------------------------------------------------------------------------

## 🧩 Folder Structure

    repayas-woocommerce-payment-gateway/
    │
    ├── repayas-payments-woocommerce.php
    ├── assets/
    └── includes/
        ├── class-wc-payment-gateway-repayas.php
        ├── repayas-ipn.php
        └── repayas-order-statuses.php

------------------------------------------------------------------------

## 📌 Requirements

-   WordPress 6.0+
-   WooCommerce 7.0+
-   PHP 8.0+
-   SSL-enabled site (recommended)

------------------------------------------------------------------------

## 📜 License

MIT License.
