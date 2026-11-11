# Tech Store

A PHP and MySQL storefront demo that takes shoppers from product browsing to a Cash on Delivery order confirmation. An administrator workspace supports catalog and order management.

## Features

- Product catalog, product details, and a browser shopping cart.
- Customer registration, sign-in, and order history.
- Cash on Delivery checkout with server-calculated totals and stock checks.
- Order confirmation with retry protection and customer-scoped receipt access.
- Administrator tools for products, users, and order status.

## Technology

PHP with PDO, MySQL, HTML, CSS, and JavaScript. Checkout uses database transactions; no external payment API is required.

## Requirements

Use patched PHP 8.4.27+ within the 8.4 series, or PHP 8.5.12+ within the 8.5 series, with PDO MySQL. The application rejects older/unsupported runtime branches. Use a maintained, patched MySQL server; the schema uses MySQL 8-compatible collations and is not claimed compatible with MariaDB. A browser with JavaScript and localStorage is required.

## Fresh local setup

1. Create a NEW empty local database and a dedicated database account. Import database.schema.sql, then database.catalog.sql. The catalog seed contains product information only; no user, order or contact-message records are seeded.
2. Set the TECHSTORE_* variables listed in .env.example in the PHP process environment. The example file is a reference; PHP does not load it automatically. Supply your own local database credentials without committing them.
3. Start the local server with a writable absolute session directory as shown below, then open http://127.0.0.1:8000/. This server is for local development.
4. Register an account at signup.html with a unique password of 12–72 bytes. To grant that account administrator access, run `php tools/promote-admin.php your-registered-email` in a terminal using the same database variables. Log out and back in. The command refuses HTTP access and creates no default password.
5. Use admin-panel.php for products, users and order status. The runtime database account needs CRUD permissions on this project's tables, not server-wide privileges. Use a separate setup account for schema creation.

PowerShell startup from the project directory:

```powershell
$sessionPath = Join-Path (Get-Location) '.test-tools\sessions'
New-Item -ItemType Directory -Path $sessionPath -Force | Out-Null
php -d "session.save_path=$sessionPath" -S 127.0.0.1:8000 router.php
```

Use an absolute path: a relative session.save_path can resolve under an endpoint's directory and cause login/CSRF state to disappear. The supplied development router denies hidden test storage; keep private files outside a production web root.

Existing databases need separately reviewed InnoDB/login_attempts changes and must allow the cod payment value. New schemas accept only cod. Do not silently convert historical payment values or import the fresh schema over real data. If cod is already supported, this checkout fix needs no schema migration: retry protection uses the existing unique order_number index. No real database was modified.

## Runtime configuration

TECHSTORE_DB_HOST, TECHSTORE_DB_NAME, TECHSTORE_DB_USER and TECHSTORE_DB_PASS configure the local database. TECHSTORE_SITE_URL is the base URL used in email links. TECHSTORE_ADMIN_EMAIL and TECHSTORE_MAIL_FROM must be configured before enabling mail. TECHSTORE_ACTION_SECRET must contain at least 32 random bytes for signed action links. Keep these values outside version control.

Mail delivery is disabled unless TECHSTORE_MAIL_ENABLED=1. A separately configured mail transport and delivery verification are required. Signed links display a confirmation page; state changes require POST and a CSRF token. Cash on Delivery (COD) is the only checkout method. No bank/card details are requested and no online charge is made. Password recovery directs users to the contact page; there is no automatic password-reset email flow.

## Checkout and confirmation

Add products, enter the delivery fields and select Place Order. Prices, shipping, coupon calculations and stock are validated on the server within a transaction. The cart is cleared only after a successful save response, then the browser opens the canonical Thank You page with the saved order number, products, server total, delivery details and COD. Failures keep the cart and form fields for retry.

Both form and JSON checkout require a CSRF token and a session/account-bound checkoutToken from php/checkout_token.php. Reusing the same attempt returns the saved order through the existing unique order number instead of reserving stock again. Attempts last 24 hours with the latest 20 retained per session; a completely new checkout attempt is a new purchase. Native form success uses HTTP 303 followed by a read-only GET confirmation. Receipt refresh never resubmits checkout or clears a new cart. Logged-in owners/admins and the originating guest session can view the appropriate receipt.

## Security and testing

CSRF tokens protect form/API changes, session IDs regenerate on login, protected requests recheck account status/role, and login attempts are throttled. Checkout derives prices and stock from the database. Cancellation/reopening adjusts stock transactionally. Order details require ownership or administrator access; guest receipts require the originating session. Dynamic product/cart/order text is escaped before HTML insertion. The order-email footer contains fictional contact information included for demonstration.

The checkout revision passed 40 HTTP/database checks and 17 checkout DOM-emulation checks. The existing 56 HTTP/security/path/JSON and 28 DOM checks were also rerun successfully, plus four fresh-schema checks. CLI administrator provisioning passed in the earlier review. The public demonstration coupon is DEMO20. DOM emulation does not verify a real browser's rendering, responsive layout or every pointer/keyboard interaction. Live mail and production deployment were not tested. Apache configuration was reviewed but not run; if using Apache 2.4, enable the supplied .htaccess rules and mod_rewrite. It fails closed without the rewrite module. Keep databases, test storage and private files outside public access.

See the [security review](PUBLIC_RELEASE_SECURITY_REPORT.md) for release scope and limitations and the [file inventory](COMMIT_FILES.txt) for the reviewed release contents.
