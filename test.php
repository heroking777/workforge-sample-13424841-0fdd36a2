<?php
declare(strict_types=1);
require __DIR__ . '/proof.php';

$failures = 0;
function check(bool $condition, string $label): void
{
    global $failures;
    if ($condition) {
        echo "ok - {$label}\n";
    } else {
        $failures++;
        fwrite(STDERR, "not ok - {$label}\n");
    }
}

$ledger = new BillingLedger(1000);
$first = $ledger->charge('order-1', 2500, 900);
check($first['status'] === 'charged' && $first['replayed'] === false, 'normal charge succeeds');

$replay = $ledger->charge('order-1', 2500, 901);
check($replay['status'] === 'charged' && $replay['replayed'] === true, 'duplicate request replays original result');
check($ledger->totalYen() === 2500, 'duplicate request does not double charge');

check($ledger->charge('order-2', 2500, 1000)['status'] === 'expired', 'expired session is rejected');
check($ledger->charge('', 2500, 900)['status'] === 'invalid', 'empty idempotency key is rejected');
check($ledger->charge('order-3', 0, 900)['status'] === 'invalid', 'non-positive amount is rejected');

$ledger->charge('order-4', 1000, 950);
check($ledger->totalYen() === 3500, 'distinct keys accumulate');

if ($failures > 0) {
    fwrite(STDERR, "{$failures} test(s) failed\n");
    exit(1);
}
echo "all 7 checks passed\n";
