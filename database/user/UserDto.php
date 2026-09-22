<?php

declare(strict_types=1);

namespace bs20php\database\user;

use DateTimeImmutable;

/**
 * Immutable data-transfer object representing a row in the `users` table.
 */
final class UserDto
{
    public function __construct(
        public readonly int                   $id,
        public readonly string                $userId,
        public readonly string                $email,
        public readonly string                $firstName,
        public readonly string                $lastName,
        public readonly string                $displayName,
        public readonly string                $role,
        public readonly bool                  $isActive,
        public readonly bool                  $isEmailVerified,
        public readonly ?string               $avatarUrl,
        public readonly ?DateTimeImmutable    $lastLoginAt,
        public readonly int                   $failedLoginCount,
        public readonly ?DateTimeImmutable    $lockedUntil,
        public readonly DateTimeImmutable     $createdAt,
        public readonly DateTimeImmutable     $updatedAt,
        public readonly ?DateTimeImmutable    $deletedAt,
    ) {}

    /** Build from a raw PDO associative-array row. */
    public static function fromRow(array $row): self
    {
        return new self(
            id:               (int)  $row['id'],
            userId:                  $row['user_id'],
            email:                   $row['email'],
            firstName:               $row['first_name'],
            lastName:                $row['last_name'],
            displayName:             $row['display_name'] ?? trim($row['first_name'] . ' ' . $row['last_name']),
            role:                    $row['role'],
            isActive:         (bool) $row['is_active'],
            isEmailVerified:  (bool) $row['is_email_verified'],
            avatarUrl:               $row['avatar_url'] ?? null,
            lastLoginAt:      self::toDateTime($row['last_login_at'] ?? null),
            failedLoginCount: (int)  ($row['failed_login_count'] ?? 0),
            lockedUntil:      self::toDateTime($row['locked_until'] ?? null),
            createdAt:        self::toDateTime($row['created_at'])  ?? new DateTimeImmutable(),
            updatedAt:        self::toDateTime($row['updated_at'])  ?? new DateTimeImmutable(),
            deletedAt:        self::toDateTime($row['deleted_at'] ?? null),
        );
    }

    private static function toDateTime(?string $value): ?DateTimeImmutable
    {
        return $value !== null ? new DateTimeImmutable($value) : null;
    }

    /** Serialize to a plain array (e.g. for JSON responses). */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'user_id'            => $this->userId,
            'email'              => $this->email,
            'first_name'         => $this->firstName,
            'last_name'          => $this->lastName,
            'display_name'       => $this->displayName,
            'role'               => $this->role,
            'is_active'          => $this->isActive,
            'is_email_verified'  => $this->isEmailVerified,
            'avatar_url'         => $this->avatarUrl,
            'last_login_at'      => $this->lastLoginAt?->format(DateTimeImmutable::ATOM),
            'failed_login_count' => $this->failedLoginCount,
            'locked_until'       => $this->lockedUntil?->format(DateTimeImmutable::ATOM),
            'created_at'         => $this->createdAt->format(DateTimeImmutable::ATOM),
            'updated_at'         => $this->updatedAt->format(DateTimeImmutable::ATOM),
            'deleted_at'         => $this->deletedAt?->format(DateTimeImmutable::ATOM),
        ];
    }
}
