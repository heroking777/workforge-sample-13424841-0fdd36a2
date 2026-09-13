# WorkForge Proof-of-Capability — CrowdWorks job 13424841

This is a small, self-contained proof built by WorkForge after reading job
13424841 ("PHP、業務システムの改善・保守　※時給2,500円〜 Webサイト更新・保守の仕事の依頼"). It is **not** the client's code, does not
use any client data, and is not presented as past production work.

## Why this proof

The job reports operational load around **session management** and **billing
design** in a PHP/CakePHP system. The smallest deterministic core of that
problem is: a retryable charge request must be applied **at most once** per
idempotency key (no double charge on retry), and an **expired session** must be
rejected.

## What it contains

- `proof.php` — dependency-free `BillingLedger` (idempotent charge + session
  expiry + input validation). Deterministic; the clock is injected.
- `test.php` — an offline test script covering normal charge, replay/duplicate,
  expiry, invalid input and accumulation.

## How it was verified

- `php test.php` executed inside WorkForge's sample sandbox (no network,
  no credentials, workspace-contained, timeout enforced).
- Secret scan: PASS.

## What this does and does not prove

It shows the implementation approach and working code for the billing/session
correctness core. It does **not** reproduce the full application. For the real
job, WorkForge would first confirm the current code and reproduce the issue,
then apply the same at-most-once / expiry handling to the actual billing and
session paths.
