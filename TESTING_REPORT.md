# Testing report — Tech Store

Review date: 2026-11-09. Status: **NOT READY** for PUBLIC publication because unverified contact data remains in the order-email template. The final review supersedes the earlier readiness claim. No GitHub publication has occurred.

## Checkout failure causes and repairs

The checkout form submitted to php/process_order.php, which rendered a second receipt inside the POST response rather than redirecting to thankyoupage.php. Refresh could resubmit a purchase, and product image paths resolved relative to php/ instead of the project root. The dedicated confirmation page omitted required shared scripts, attempted unconditional cart clearing, omitted delivery/payment details and claimed an email was sent without delivery evidence.

The local preview also used a relative PHP session path, causing session cookies/CSRF state to be replaced between requests. The preview now uses an absolute directory inside ignored test storage; README documents this. DOM submission tests exposed a separate fresh-page race in which concurrent catalog and token requests could create different sessions. Shared requests now establish the same CSRF session first.

Repairs retain the checkout layout and styling, remove bank/check controls, show COD as the sole selected method, and reject every other payment value server-side. One canonical confirmation page displays saved products, total, delivery details, order number and COD. The successful frontend response clears only the submitted cart before navigation. Failures keep cart and delivery fields. Receipt GET/refresh never creates an order or clears a new cart. Native forms use Post/Redirect/Get with HTTP 303. Server-issued, session/account-bound attempt tokens produce a stable unique order number, allowing safe retries without a new stock reservation. Historical payment records are not rewritten.

## Latest passed tests

| Suite | Checks | Result |
|---|---:|---|
| COD HTTP/database | 40 | PASS |
| Checkout script integration using JSDOM and real HTTP | 17 | PASS |
| Existing HTTP functional regression | 25 | PASS |
| Existing HTTP security regression | 23 | PASS |
| Existing throttling/private-path regression | 5 | PASS |
| Existing JSON authentication/input regression | 3 | PASS |
| Existing page/cart/product/coupon/XSS DOM regression | 28 | PASS |
| Fresh schema/catalog import and COD enum | 4 | PASS |
| PHP syntax / JavaScript syntax | 26 / 13 files | PASS |

The 40 new HTTP/database checks verify: COD-only interface; native 303 redirect; correct receipt number/products/total/payment/delivery; no false mail promise/unconditional cart clearing; persisted COD/price/stock; refresh and replay without duplication; account/anonymous receipt denial; bank/check/card/paypal/missing-method rejection on both endpoints; empty/invalid/out-of-stock carts; partial stock rollback; missing/malformed delivery; no database/stock changes on failures; invalid/cross-session token denial; JSON success/redirect; parallel retries returning one order; guest ownership; admin listing/cancel/repeat/reopen and customer denial of admin changes.

The 17 checkout DOM checks verify: fresh-session token initialization; cart retention and disabled submit while pending; one request on double click; clearing only after success; canonical redirect invocation; authorized receipt details; no script errors; a new cart surviving authorized receipt load/refresh; out-of-stock failure retaining cart/form fields; network failure retaining cart; committed response loss followed by safe retry returning the same saved order; concurrent cart changes preserved; empty-cart client error; missing receipts cannot clear carts.

HTTP tests exercised PHP 8.4.27 on loopback and inspected actual records in the isolated disposable MySQL database. All fixture accounts, products, orders, addresses and test data were synthetic. Fresh-schema tests created separate disposable schemas. An older installed PHP was used only as the SQL fixture/lint driver, not to serve the application. The application rejects that older runtime. No production database, real payment provider or external email service was contacted.

## Exact application and preparation file changes

These paths are repository-relative. COMMIT_FILES.txt is the complete proposed publication allowlist, not just this change list.

Application/schema files changed:

- checkout.html
- database.schema.sql
- js/checkout.js
- js/security.js
- php/Orders.php
- php/checkout.php
- php/process_order.php
- php/email.php
- thankyoupage.php

Application files added:

- php/CheckoutService.php
- php/checkout_token.php

Preparation/documentation files updated:

- README.md
- TESTING_REPORT.md
- FINAL_REVIEW.md
- GITHUB_PREPARATION.md
- PUBLIC_RELEASE_SECURITY_REPORT.md
- COMMIT_FILES.txt

Ignored test scripts added/updated: .test-tools/checkout-db.php, .test-tools/checkout-http.mjs, .test-tools/checkout-dom.cjs, .test-tools/http-tests.mjs, .test-tools/security-http.mjs, .test-tools/dom-tests.cjs, .test-tools/fresh-schema-test.php and .test-tools/checkout-review.cjs. Generated scanner/test results, dependencies, fixture data, inventories and candidate snapshots remain under .test-tools and are excluded. Original archives, inspection copies and other project source folders were not modified.

## Reproduction and limits

README describes patched PHP/PDO MySQL, a new database, clean schema/catalog import, environment variables, an absolute session directory, registration and CLI administrator provisioning. Existing databases that already support cod need no schema change for attempt deduplication; historical payments must not be silently converted. Test scripts rely on excluded local fixtures and are not public setup requirements.

Real-browser rendering, responsive layout, pointer/keyboard interactions, live mail delivery, Apache execution, exhaustive concurrency and a full penetration test remain unverified. DOM tests use an observable navigation hook and actual HTTP responses; they do not prove browser visual behavior. Attempt deduplication lasts 24 hours for the latest 20 attempts in the same session/account; a new attempt is a separate purchase. Mail stays disabled in testing, and checkout only records COD orders without online charging.

No repository was initialized, committed, pushed or uploaded.
## Final independent publication review — 2026-11-09

A final review on 2026-11-09 found unverified non-placeholder contact data in php/email.php:115-116 (email, telephone and street address). Values are intentionally withheld. Replace them with demonstration placeholders or explicitly establish that they are intended public business details before publication. Application source was left unchanged for this review.

All 145 functional/security/schema checks were rerun successfully: 40 COD HTTP/database, 17 checkout DOM, 25 earlier HTTP, 23 security HTTP, five throttle/path, three JSON authentication, 28 earlier DOM and four clean-schema checks. All 26 PHP and 13 JavaScript files pass syntax checks. HTTP used the existing dedicated synthetic MySQL/PHP preview on loopback ports 34077/38081; no production database was contacted. Serving runtime reports PHP 8.4.27. The old installed PHP was only a local fixture/lint driver, not the application server.

The exact 174-file proposed set matches COMMIT_FILES.txt and .gitignore, with no source differences from the preceding reviewed candidate. Redacted Gitleaks and supplementary secret/private-path/hash patterns found no credential values. A broader personal-data scan found the contact footer missed by the earlier readiness assessment. No project or inherited Git repository exists, so there is no commit history to publish. Candidate SHA256 hashes are retained in ignored audit storage.

PNG text metadata contained creation/modification timestamps or a software name; no GPS/owner/serial tags were detected by the implemented metadata checks. A contact sheet of 70 raster assets and selected additional images was inspected; complete full-resolution visual/OCR/steganographic and asset-rights certification was not performed. Font Awesome 6.4.0 had no returned OSV advisory, and its CDN SHA384 integrity matched. These checks do not prove absence of undiscovered vulnerabilities.

Remaining deployment risks include unthrottled registration/contact/guest-order abuse (login throttling is present), raw database exception details in some server logs, and unverified Apache/HTTPS/production configuration. Live email, automated password reset, full real-browser rendering/accessibility and exhaustive concurrency/load/penetration coverage remain unavailable or unverified. The private router paths, ownership checks, CSRF and tested stock transactions passed. No source behavior was modified or Git operation performed.
