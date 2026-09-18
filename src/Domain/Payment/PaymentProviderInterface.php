<?php

declare(strict_types=1);

namespace App\Domain\Payment;

interface PaymentProviderInterface
{
    public function charge(int $amount, array $paymentDetails): PaymentResult;
}
