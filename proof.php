<?php
declare(strict_types=1);

/**
 * WorkForge Proof-of-Capability (generated sample, not client code).
 *
 * Target job: CrowdWorks #13424841 (PHP / CakePHP maintenance).
 * The job body reports operational load around session management and billing
 * design. This dependency-free module demonstrates the core of that problem:
 * a retryable billing operation must be applied AT MOST ONCE per idempotency
 * key, and a checkout session that has already expired must be rejected.
 *
 * Deliberately offline: no framework, no DB, no network, no credentials.
 * An in-memory ledger plus an injected clock keep every behavior deterministic.
 */

final class BillingLedger
{
    /** @var array<string, array{amount:int}> */
    private array $charges = [];

    public function __construct(private int $sessionExpiresAt)
    {
    }

    /** @return array{status:string, amount?:int, replayed?:bool, reason?:string} */
    public function charge(string $idempotencyKey, int $amountYen, int $now): array
    {
        if ($idempotencyKey === '') {
            return ['status' => 'invalid', 'reason' => 'empty_idempotency_key'];
        }
        if ($amountYen <= 0) {
            return ['status' => 'invalid', 'reason' => 'non_positive_amount'];
        }
        if ($now >= $this->sessionExpiresAt) {
            return ['status' => 'expired'];
        }
        if (array_key_exists($idempotencyKey, $this->charges)) {
            return [
                'status' => 'charged',
                'amount' => $this->charges[$idempotencyKey]['amount'],
                'replayed' => true,
            ];
        }
        $this->charges[$idempotencyKey] = ['amount' => $amountYen];
        return ['status' => 'charged', 'amount' => $amountYen, 'replayed' => false];
    }

    public function totalYen(): int
    {
        $total = 0;
        foreach ($this->charges as $charge) {
            $total += $charge['amount'];
        }
        return $total;
    }
}
