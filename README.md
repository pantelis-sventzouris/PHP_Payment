# EveryPay PHP Technical Challenge

A small payment service implemented with **plain PHP 8.2+** without using any framework.

The application displays the following:

- REST API endpoint for charging a merchant's configured fake PSP
- API-key authentication
- Multiple merchants
- `fakeStripe` and `fakePaypal` providers
- SQLite persistence through repository interfaces
- CLI charge reporting
- Email delivery through Symfony Mailer
- Mailpit for local email inspection
- PHPUnit unit/integration tests
- Dockerized local environment

## Architecture

The application separates business logic from infrastructure concerns:

```text
HTTP
 |
ChargeController
 |
ChargeService
 |---------------- PaymentProviderInterface
 |                    |
 |             +------+------+
 |             |             |
 |        fakeStripe    fakePaypal
 |
ChargeRepositoryInterface
 |
PdoChargeRepository
 |
SQLite
```

Reporting follows the same principle:

```text
CLI -> ChargeReportService
          |       |
          |       +-> MailerInterface
          |
          +-> ChargeRepositoryInterface
```

The service layer does not depend directly on SQLite, which means the persistence implementation can be replaced later.

## Requirements

Only Docker and Docker Compose are required.

## Run

Build the containers:

```bash
docker compose up -d --build
```

Create the SQLite schema:

```bash
docker compose exec app php bin/migrate.php
```

Seed two demo merchants:

```bash
docker compose exec app php bin/seed.php
```

The API is available at:

```text
http://localhost:8080
```

Mailpit is available at:

```text
http://localhost:8025
```

## Demo merchants

### fakeStripe

```text
API key: stripe-merchant-secret
Provider: fakeStripe
```

### fakePaypal

```text
API key: paypal-merchant-secret
Provider: fakePaypal
```

API keys are hashed with SHA-256 before being stored in the database.

## Charge with fakeStripe

```bash
curl -i \
  -X POST http://localhost:8080/charges \
  -H "Content-Type: application/json" \
  -H "X-API-Key: stripe-merchant-secret" \
  -d '{
    "amount": 1999,
    "card_number": "4242424242424242",
    "cvv": "123",
    "expiry_month": 12,
    "expiry_year": 2030
  }'
```

Successful response:

```json
{
  "id": 1,
  "status": "successful",
  "amount": 1999,
  "currency": "EUR",
  "provider": "fakeStripe",
  "provider_reference": "stripe_...",
  "failure_reason": null,
  "created_at": "..."
}
```

### Simulate a Stripe decline

Any valid card number ending in `0000` is declined.

Example:

```text
4242424242420000
```

## Charge with fakePaypal

```bash
curl -i \
  -X POST http://localhost:8080/charges \
  -H "Content-Type: application/json" \
  -H "X-API-Key: paypal-merchant-secret" \
  -d '{
    "amount": 2499,
    "email": "customer@example.com",
    "password": "secret123"
  }'
```

### Simulate a PayPal decline

Use:

```text
fail@example.com
```

as the PayPal email.

## HTTP status codes

| Situation | HTTP status |
|---|---:|
| Successful charge | 201 |
| Missing/invalid API key | 401 |
| Provider declined charge | 402 |
| Invalid request | 422 |
| Unknown route | 404 |
| Unexpected server error | 500 |

Both successful and failed charge attempts are persisted.

## Send a charge report

Using an explicit date range:

```bash
docker compose exec app php bin/report.php \
  --merchant=1 \
  --from=2026-09-01 \
  --to=2026-09-18
```

Or use the last N days:

```bash
docker compose exec app php bin/report.php \
  --merchant=1 \
  --days=7
```

Open Mailpit at:

```text
http://localhost:8025
```

to view the generated report email.

## Run tests

```bash
docker compose exec app vendor/bin/phpunit
```

or:

```bash
docker compose exec app composer test
```

## Assumptions and trade-offs

- Monetary amounts are represented as integer cents. `1999` means EUR 19.99.
- The challenge uses EUR only, although `currency` is persisted separately.
- Each merchant has exactly one configured PSP.
- A merchant is determined from its API key. A client cannot choose a merchant ID in a charge request.
- `fakeStripe` rejects cards ending in `0000`.
- `fakePaypal` rejects `fail@example.com`.
- The fake-provider rules are deterministic so automated tests are repeatable.
- Full card numbers, CVVs, and PayPal passwords are never persisted.
- Only the last four digits of a fakeStripe card are stored.
- SQLite was selected to keep the exercise easy to run.
- Repository interfaces isolate application logic from the persistence implementation.
- Symfony Mailer is used only as an email transport abstraction; Mailpit is used locally.
- The PHP built-in HTTP server is sufficient for this technical exercise. A production deployment would normally use PHP-FPM behind a web server such as Nginx.
- Error responses intentionally avoid exposing exception details for unexpected failures.

## Security notes

A real payment gateway should avoid receiving or storing raw card credentials wherever possible and would require substantially stronger security controls,
secrets management, observability, auditing, rate limiting, TLS configuration, tokenization, and provider-specific compliance requirements.
