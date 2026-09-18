<?php

declare(strict_types=1);

namespace App\Infrastructure\Payment;

use App\Domain\Payment\PaymentProviderInterface;
use App\Domain\Payment\PaymentResult;
use InvalidArgumentException;

final class FakePaypalProvider implements PaymentProviderInterface
{
    public function charge(int $amount, array $paymentDetails): PaymentResult
    {
        $email = $paymentDetails['email'] ?? null;
        $password = $paymentDetails['password'] ?? null;

        if (!is_string($email) || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('A valid email is required');
        }

        if (!is_string($password) || $password === '') {
            throw new InvalidArgumentException('Password is required');
        }

        // Deterministic fake decline rule for repeatable tests.
        if ($email === 'fail@example.com') {
            return PaymentResult::failure('PayPal payment rejected');
        }

        return PaymentResult::success('paypal_' . bin2hex(random_bytes(8)));
    }
}
