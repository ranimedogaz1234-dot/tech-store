# Final PUBLIC publication security review — Tech Store

Review date: 2026-11-09. Verdict: **NOT READY**. This supersedes the previous READY FOR PUBLICATION assessment, which missed a contact block in an email template. This was a review, not a source-code repair or production penetration test. No Git/GitHub operation occurred.

## Publication blocker

php/email.php:115-116 still contains a non-placeholder contact email, telephone number and street address. Their ownership and intended public status have not been established. Actual values are deliberately omitted from this report. Even with mail disabled, publishing source reveals this block.

Before publication, replace those details with demonstration placeholders or explicitly verify that they are authorized public business information. Then review the exact resulting candidate and rerun its privacy/secret scan. The application source was left unchanged during this review so this finding remains unresolved.

## Exact files, history and secrets

The proposed set contains 174 files: 79 text/source/configuration documents and 95 raster assets. COMMIT_FILES.txt matches the current nonignored files exactly. A fresh candidate was copied and SHA256-hashed; application/source content matched the previous reviewed candidate before report updates. Reports are included and re-reviewed after this audit.

Neither a project .git file/directory nor an inherited Git repository exists. Therefore there are no project commits, branches, tags or historical blobs available to scan or include. No history was initialized. A future repository must start inside this project with only the reviewed allowlist, without importing unrelated/old history or forcing ignored files.

Redacted Gitleaks 8.30.1 and supplementary private-key, provider-token, credential-URL, credential-literal, password-hash and private-home-path checks found no exposed credential values in the proposed set. This does not establish whether historical credentials outside this set are active. Actual local credentials, session files, test databases, runtimes/tools, scans and fixtures remain under ignored .test-tools. A previously identified personal screenshot is also ignored and absent from the candidate. .env.example has placeholders only.

Clean schema/catalog SQL seed products, colors, images and specifications; fresh import tests verify that users, orders and contact_messages start empty. No populated customer dump is proposed. No automatic shell execution, downloader, committed executable or GitHub workflow was found in the application publication set. Publishing source does not itself start a server, open a port or grant access to a local database.

## Latest workflow and security tests

All 145 checks passed again on isolated loopback PHP 8.4.27 and the dedicated synthetic MySQL instance:

| Suite | Passed |
|---|---:|
| COD checkout, receipt, database and administrator order flow | 40 |
| Checkout/cart/confirmation JavaScript with real local HTTP | 17 |
| Earlier functional HTTP | 25 |
| HTTP security | 23 |
| Login throttle/private paths | 5 |
| JSON authentication/input | 3 |
| Earlier page/cart/product/coupon/XSS DOM | 28 |
| Fresh schema/catalog | 4 |

All 26 PHP and 13 JavaScript files passed syntax checks. COD is the sole accepted new-order payment. Tests confirmed native HTTP 303 redirect, saved order/product/total/delivery/COD details, cart clearing only after success, cart retention on failures, read-only receipt refresh, bounded retry deduplication, foreign/anonymous receipt denial, server pricing, unavailable-stock rollback and retained administrator order management. No real payment, live email or production data was used.

The serving PHP version was checked and official PHP release/support information reviewed. Font Awesome 6.4.0 returned no known OSV advisory, and the downloaded public CDN stylesheet matched the pinned SHA384 integrity. These are current known-advisory checks, not a guarantee about native runtimes or all build/test tooling.

## Personal-data and asset review

The contact footer is the unresolved privacy finding. Metadata parsing detected no GPS/owner/serial tags; identified PNG text chunks contained image timestamps or a software name. A 70-image contact sheet and selected additional assets were inspected. This is not complete full-resolution OCR, steganographic analysis, personal-consent verification or image-rights certification. Third-party product/marketing images still need appropriate publication rights.

## Remaining vulnerabilities and limits

- Registration, contact submission and guest COD orders lack comprehensive anti-automation/rate controls. Bots could create accounts/messages or reserve inventory if this were exposed online. CSRF protection and login throttling do not prevent that abuse.
- Some server-side catch blocks log raw database exception messages. Logs can contain configuration or submitted values and must remain private; use redacted operational logging before handling real data.
- Apache rules were reviewed but not executed; production HTTPS/cookies, request limits, private-file placement and database privileges require deployment review. The development server must stay local.
- Real-browser visual/navigation/accessibility coverage, live mail transport, all administrative form combinations, exhaustive stock concurrency/load and a full penetration test remain incomplete. Password reset is not automated.
- Checkout retry deduplication is limited to the same session/account-issued attempt: 24 hours, latest 20 attempts. A new attempt is a separate purchase.
- No complete computer/account audit or verification of historical credential usage was performed. Rotate prior exposed/reused credentials if they were ever real.

**Required before PUBLIC publication:** resolve the contact block and rescan the exact changed candidate. The remaining deployment limitations must remain clearly documented; this project is not certified for internet production use.

No original archive, untouched inspection copy or unrelated project was modified. No commit, repository creation, push or upload occurred.

Sources: [PHP 8.4.27 security release](https://www.php.net/releases/8_4_27.php), [supported PHP branches](https://www.php.net/supported-versions.php/), [OSV public vulnerability database](https://osv.dev/).