<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Domain\Payment\PaymentProviderInterface;
use App\Domain\Payment\PaymentResult;
use InvalidArgumentException;

final class FakeStripeProvider implements PaymentProviderInterface
{
    public function charge(int $amount, array $paymentDetails): PaymentResult
    {
        foreach (['card_number', 'cvv', 'expiry_month', 'expiry_year'] as $field) {
            if (!isset($paymentDetails[$field]) || $paymentDetails[$field] === '') {
                throw new InvalidArgumentException("Missing field: {$field}");
            }
        }

        $cardNumber = preg_replace('/\s+/', '', (string) $paymentDetails['card_number']);
        $cvv = (string) $paymentDetails['cvv'];
        $month = (int) $paymentDetails['expiry_month'];
        $year = (int) $paymentDetails['expiry_year'];

        if (!preg_match('/^\d{12,19}$/', $cardNumber)) {
            throw new InvalidArgumentException('Invalid card number');
        }

        if (!preg_match('/^\d{3,4}$/', $cvv)) {
            throw new InvalidArgumentException('Invalid CVV');
        }

        if ($month < 1 || $month > 12) {
            throw new InvalidArgumentException('Invalid expiry month');
        }

        if ($year < (int) date('Y')) {
            throw new InvalidArgumentException('Card has expired');
        }

        // Deterministic fake decline rule for repeatable tests.
        if (str_ends_with($cardNumber, '0000')) {
            return PaymentResult::failure('Card declined');
        }

        return PaymentResult::success('stripe_' . bin2hex(random_bytes(8)));
    }
}
