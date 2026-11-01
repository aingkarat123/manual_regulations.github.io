<?php
declare(strict_types=1);

const POLICY_GUEST_TOKEN_HASH = '89cf646edd6959e67a6a4afc4f29ac196802864c446a08867e66bc874f714002';
const POLICY_GUEST_SESSION_TTL = 43200;

if (!headers_sent()) {
    header('Cache-Control: private, no-store, no-cache, must-revalidate, max-age=0');
    header('X-Robots-Tag: noindex, nofollow');
}

function policyAccessGranted(): bool
{
    if (isset($_SESSION['User_name'])) {
        return true;
    }

    $startedAt = (int) ($_SESSION['policy_guest_started_at'] ?? 0);
    if (!empty($_SESSION['policy_guest']) && $startedAt > 0 && $startedAt >= (time() - POLICY_GUEST_SESSION_TTL)) {
        return true;
    }

    unset($_SESSION['policy_guest'], $_SESSION['policy_guest_started_at']);
    return false;
}

function policyGuestAccess(): bool
{
    return !isset($_SESSION['User_name']) && policyAccessGranted();
}

