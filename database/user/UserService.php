<?php

declare(strict_types=1);

namespace bs20php\database\user;

use RuntimeException;

/**
 * Service layer that orchestrates business rules around users.
 *
 * Controllers / API handlers should call this instead of the repository
 * directly so that validation and business logic stay in one place.
 */
final class UserService
{
    public function __construct(
        private readonly UserRepository $repo,
    ) {}

    // =========================================================
    //  CREATE
    // =========================================================

    public function register(
        string  $userId,
        string  $email,
        string  $plainPassword,
        string  $firstName    = '',
        string  $lastName     = '',
        string  $role         = 'user',
        ?string $avatarUrl    = null,
    ): UserDto {
        // Guard: duplicate checks happen at DB level, but we provide
        // a friendlier early-exit for duplicate email via service layer.
        if ($this->repo->findByEmail($email) !== null) {
            throw new RuntimeException("Email '{$email}' is already registered.", 409);
        }
        if ($this->repo->findByUserId($userId) !== null) {
            throw new RuntimeException("User ID '{$userId}' is already taken.", 409);
        }

        $input = new CreateUserInput(
            userId:        $userId,
            email:         $email,
            plainPassword: $plainPassword,
            firstName:     $firstName,
            lastName:      $lastName,
            role:          $role,
            avatarUrl:     $avatarUrl,
        );

        return $this->repo->create($input);
    }

    // =========================================================
    //  READ
    // =========================================================

    public function getById(int $id): UserDto
    {
        return $this->repo->findById($id)
            ?? throw new RuntimeException("User #{$id} not found.", 404);
    }

    public function getByUserId(string $userId): UserDto
    {
        return $this->repo->findByUserId($userId)
            ?? throw new RuntimeException("User '{$userId}' not found.", 404);
    }

    public function getByEmail(string $email): UserDto
    {
        return $this->repo->findByEmail($email)
            ?? throw new RuntimeException("No user with email '{$email}'.", 404);
    }

    /**
     * @param  array<string,mixed> $filters
     * @return array{data: UserDto[], total: int, page: int, per_page: int}
     */
    public function paginate(
        int   $page    = 1,
        int   $perPage = 25,
        array $filters = [],
    ): array {
        $page    = max(1, $page);
        $perPage = min(100, max(1, $perPage));
        $offset  = ($page - 1) * $perPage;

        return [
            'data'     => $this->repo->findAll(limit: $perPage, offset: $offset, filters: $filters),
            'total'    => $this->repo->count($filters),
            'page'     => $page,
            'per_page' => $perPage,
        ];
    }

    // =========================================================
    //  UPDATE
    // =========================================================

    public function update(int $id, array $fields): UserDto
    {
        // Ensure user exists first.
        $this->getById($id);

        $input = new UpdateUserInput(
            email:           $fields['email']             ?? null,
            firstName:       $fields['first_name']        ?? null,
            lastName:        $fields['last_name']         ?? null,
            role:            $fields['role']              ?? null,
            isActive:        isset($fields['is_active'])
                                 ? (bool) $fields['is_active'] : null,
            isEmailVerified: isset($fields['is_email_verified'])
                                 ? (bool) $fields['is_email_verified'] : null,
            avatarUrl:       $fields['avatar_url']        ?? null,
        );

        return $this->repo->update($id, $input)
            ?? throw new RuntimeException("Update failed for user #{$id}.", 500);
    }

    public function changePassword(int $id, string $currentPassword, string $newPassword): void
    {
        if (!$this->repo->verifyPassword($id, $currentPassword)) {
            throw new RuntimeException('Current password is incorrect.', 403);
        }
        $this->repo->updatePassword($id, $newPassword);
    }

    // =========================================================
    //  DELETE / RESTORE
    // =========================================================

    public function deactivate(int $id): void
    {
        if (!$this->repo->softDelete($id)) {
            throw new RuntimeException("User #{$id} not found or already deleted.", 404);
        }
    }

    public function restore(int $id): void
    {
        if (!$this->repo->restore($id)) {
            throw new RuntimeException("User #{$id} not found or is not deleted.", 404);
        }
    }

    public function permanentlyDelete(int $id): void
    {
        if (!$this->repo->hardDelete($id)) {
            throw new RuntimeException("User #{$id} not found.", 404);
        }
    }

    // =========================================================
    //  AUTHENTICATION
    // =========================================================

    /**
     * Authenticate by user_id + password.
     * Throws on bad credentials, locked account, or inactive user.
     */
    public function authenticate(string $userId, string $plainPassword): UserDto
    {
        $user = $this->repo->findByUserId($userId);

        if ($user === null) {
            // Avoid user enumeration: same error for unknown user.
            throw new RuntimeException('Invalid credentials.', 401);
        }

        if ($this->repo->isLocked($user->id)) {
            throw new RuntimeException('Account is temporarily locked. Please try again later.', 423);
        }

        if (!$user->isActive) {
            throw new RuntimeException('Account is disabled. Contact support.', 403);
        }

        if (!$this->repo->verifyPassword($user->id, $plainPassword)) {
            $this->repo->recordFailedLogin($user->id);
            throw new RuntimeException('Invalid credentials.', 401);
        }

        $this->repo->recordSuccessfulLogin($user->id);

        return $this->getById($user->id); // reload refreshed DTO
    }
}
