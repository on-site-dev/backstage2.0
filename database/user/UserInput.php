<?php

declare(strict_types=1);

namespace bs20php\database\user;

use InvalidArgumentException;

/**
 * Validated input for creating a new user.
 */
final class CreateUserInput
{
    public readonly string $passwordHash;

    private const VALID_ROLES = ['admin', 'manager', 'user', 'viewer'];

    public function __construct(
        public readonly string  $userId,
        public readonly string  $email,
        string                  $plainPassword,
        public readonly string  $firstName    = '',
        public readonly string  $lastName     = '',
        public readonly string  $role         = 'user',
        public readonly bool    $isActive     = true,
        public readonly ?string $avatarUrl    = null,
    ) {
        $this->validate();
        $this->passwordHash = self::hashPassword($plainPassword);
    }

    private function validate(): void
    {
        if (trim($this->userId) === '') {
            throw new InvalidArgumentException('user_id cannot be empty.');
        }
        if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$this->email}");
        }
        if (!in_array($this->role, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException(
                "Invalid role '{$this->role}'. Allowed: " . implode(', ', self::VALID_ROLES)
            );
        }
    }

    private static function hashPassword(string $plain): string
    {
        if (strlen($plain) < 8) {
            throw new InvalidArgumentException('Password must be at least 8 characters.');
        }
        return password_hash($plain, PASSWORD_ARGON2ID);
    }
}


/**
 * Validated input for updating an existing user.
 * All fields are optional — only non-null values will be applied.
 */
final class UpdateUserInput
{
    private const VALID_ROLES = ['admin', 'manager', 'user', 'viewer'];

    public function __construct(
        public readonly ?string $email        = null,
        public readonly ?string $firstName    = null,
        public readonly ?string $lastName     = null,
        public readonly ?string $role         = null,
        public readonly ?bool   $isActive     = null,
        public readonly ?bool   $isEmailVerified = null,
        public readonly ?string $avatarUrl    = null,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->email !== null && !filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Invalid email address: {$this->email}");
        }
        if ($this->role !== null && !in_array($this->role, self::VALID_ROLES, true)) {
            throw new InvalidArgumentException(
                "Invalid role '{$this->role}'. Allowed: " . implode(', ', self::VALID_ROLES)
            );
        }
    }

    /** Returns only the fields that were explicitly set (non-null). */
    public function toChangeset(): array
    {
        $map = [
            'email'            => $this->email,
            'first_name'       => $this->firstName,
            'last_name'        => $this->lastName,
            'role'             => $this->role,
            'is_active'        => $this->isActive,
            'is_email_verified'=> $this->isEmailVerified,
            'avatar_url'       => $this->avatarUrl,
        ];

        return array_filter($map, fn($v) => $v !== null);
    }
}
