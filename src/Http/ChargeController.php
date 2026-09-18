<?php

declare(strict_types=1);

namespace App\Http;

use App\Application\ChargeService;
use InvalidArgumentException;
use Throwable;

final class ChargeController
{
    public function __construct(
        private readonly Authenticator $authenticator,
        private readonly ChargeService $chargeService,
    ) {
    }

    public function store(): never
    {
        try {
            $merchant = $this->authenticator->authenticate(
                $_SERVER['HTTP_X_API_KEY'] ?? null
            );

            $raw = file_get_contents('php://input');
            $payload = json_decode($raw ?: '{}', true, 512, JSON_THROW_ON_ERROR);

            if (!is_array($payload)) {
                throw new InvalidArgumentException('JSON object expected');
            }

            $amount = filter_var(
                $payload['amount'] ?? null,
                FILTER_VALIDATE_INT
            );

            if ($amount === false) {
                throw new InvalidArgumentException('Amount must be an integer number of cents');
            }

            unset($payload['amount']);

            $charge = $this->chargeService->charge(
                $merchant,
                (int) $amount,
                $payload
            );

            JsonResponse::send([
                'id' => $charge->id,
                'status' => $charge->status,
                'amount' => $charge->amount,
                'currency' => $charge->currency,
                'provider' => $charge->provider,
                'provider_reference' => $charge->providerReference,
                'failure_reason' => $charge->failureReason,
                'created_at' => $charge->createdAt->format(DATE_ATOM),
            ], $charge->status === 'successful' ? 201 : 402);
        } catch (AuthenticationException $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 401);
        } catch (\JsonException|InvalidArgumentException $exception) {
            JsonResponse::send(['error' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            JsonResponse::send(['error' => 'Internal server error'], 500);
        }
    }
}
