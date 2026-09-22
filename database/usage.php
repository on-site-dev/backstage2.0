<?php

declare(strict_types=1);

/**
 * ============================================================
 *  Backstage 2.0 — Users Module Usage Examples
 * ============================================================
 *
 *  Autoload (Composer PSR-4):
 *    "Backstage\\": "src/"
 *
 *  Environment variables required:
 *    DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS, DB_SCHEMA
 * ============================================================
 */

require_once __DIR__ . '/vendor/autoload.php';

use bs20php\database\user\UserRepository;
use bs20php\database\user\UserService;

// -- Bootstrap -------------------------------------------------------
$repo    = new UserRepository();          // Uses Connection singleton
$service = new UserService($repo);


// ====================================================================
//  1. CREATE — Register a new user
// ====================================================================
try {
    $newUser = $service->register(
        userId:        'jsmith',
        email:         'jane.smith@example.com',
        plainPassword: 'S3cure!Pass',
        firstName:     'Jane',
        lastName:      'Smith',
        role:          'user',
    );

    echo "Created user #{$newUser->id}: {$newUser->displayName}\n";
    // → Created user #1: Jane Smith

} catch (RuntimeException $e) {
    echo "Error ({$e->getCode()}): {$e->getMessage()}\n";
}


// ====================================================================
//  2. READ — Fetch by various identifiers
// ====================================================================

// By surrogate ID
$user = $repo->findById(1);
echo $user?->email . "\n";                // jane.smith@example.com

// By login handle
$user = $repo->findByUserId('jsmith');
echo $user?->role . "\n";                 // user

// By email
$user = $repo->findByEmail('jane.smith@example.com');
echo $user?->displayName . "\n";          // Jane Smith


// ====================================================================
//  3. READ — Paginated list with filters
// ====================================================================

$result = $service->paginate(page: 1, perPage: 10, filters: ['role' => 'user']);

echo "Showing {$result['per_page']} of {$result['total']} users\n";

foreach ($result['data'] as $u) {
    echo "  [{$u->role}] {$u->userId} — {$u->email}\n";
}


// ====================================================================
//  4. UPDATE — Partial field update
// ====================================================================

try {
    $updated = $service->update(1, [
        'first_name' => 'Janet',
        'role'       => 'manager',
        'is_active'  => true,
    ]);

    echo "Updated: {$updated->displayName} is now a {$updated->role}\n";
    // → Updated: Janet Smith is now a manager

} catch (RuntimeException $e) {
    echo "Update error: {$e->getMessage()}\n";
}


// ====================================================================
//  5. PASSWORD — Change password
// ====================================================================

try {
    $service->changePassword(
        id:              1,
        currentPassword: 'S3cure!Pass',
        newPassword:     'N3wS3cure!Pass',
    );
    echo "Password updated successfully.\n";

} catch (RuntimeException $e) {
    echo "Password error ({$e->getCode()}): {$e->getMessage()}\n";
}


// ====================================================================
//  6. AUTHENTICATE — Login flow
// ====================================================================

try {
    $authed = $service->authenticate('jsmith', 'N3wS3cure!Pass');
    echo "Authenticated: {$authed->displayName}, last login: "
       . ($authed->lastLoginAt?->format('Y-m-d H:i:s') ?? 'never') . "\n";

} catch (RuntimeException $e) {
    // Codes: 401 = bad credentials, 423 = locked, 403 = inactive
    echo "Auth failed ({$e->getCode()}): {$e->getMessage()}\n";
}


// ====================================================================
//  7. SOFT DELETE — Deactivate & restore
// ====================================================================

$service->deactivate(1);
echo "User #1 soft-deleted.\n";

// findById now returns null (deleted_at IS NOT NULL)
$gone = $repo->findById(1);
echo ($gone === null ? "Not found (as expected)\n" : "Still visible\n");

// Pass true to see soft-deleted records
$tombstone = $repo->findById(1, includeDeleted: true);
echo "Tombstone email: {$tombstone?->email}\n";

// Restore
$service->restore(1);
echo "User #1 restored.\n";


// ====================================================================
//  8. HARD DELETE — Permanent removal
// ====================================================================

// $service->permanentlyDelete(1);
// echo "User #1 permanently removed.\n";


// ====================================================================
//  9. JSON response example (API controller pattern)
// ====================================================================

function jsonResponse(int $status, mixed $data): void
{
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

// Example: GET /api/users/1
try {
    $user = $service->getById(1);
    jsonResponse(200, ['data' => $user->toArray()]);
} catch (RuntimeException $e) {
    jsonResponse($e->getCode() ?: 500, ['error' => $e->getMessage()]);
}
