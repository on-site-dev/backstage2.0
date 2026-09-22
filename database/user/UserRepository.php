<?php

declare(strict_types=1);

namespace bs20php\database\user;

use bs20php\database\Connection;
use PDO;
use PDOException;
use RuntimeException;

/**
 * Repository for all CRUD operations on the `users` table.
 *
 * All "find" methods exclude soft-deleted rows by default.
 * Pass $includeDeleted = true where tombstone records are needed.
 */
final class UserRepository
{
    private PDO $pdo;

    public function __construct(?PDO $pdo = null)
    {
        $this->pdo = $pdo ?? Connection::getInstance();
    }

    // =========================================================
    //  CREATE
    // =========================================================

    /**
     * Insert a new user row and return the populated DTO.
     *
     * @throws RuntimeException on duplicate user_id / email or DB error.
     */
    public function create(CreateUserInput $input): UserDto
    {
        $sql = <<<SQL
            INSERT INTO users
                (user_id, email, password_hash, first_name, last_name,
                 role, is_active, avatar_url)
            VALUES
                (:user_id, :email, :password_hash, :first_name, :last_name,
                 :role, :is_active, :avatar_url)
            RETURNING *
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':user_id'       => $input->userId,
                ':email'         => strtolower(trim($input->email)),
                ':password_hash' => $input->passwordHash,
                ':first_name'    => $input->firstName,
                ':last_name'     => $input->lastName,
                ':role'          => $input->role,
                ':is_active'     => $input->isActive ? 'true' : 'false',
                ':avatar_url'    => $input->avatarUrl,
            ]);

            $row = $stmt->fetch();
            if ($row === false) {
                throw new RuntimeException('INSERT returned no rows.');
            }

            return UserDto::fromRow($row);

        } catch (PDOException $e) {
            // PostgreSQL unique-violation code = 23505
            if (str_contains($e->getMessage(), '23505')) {
                throw new RuntimeException(
                    'A user with that user_id or email already exists.',
                    409,
                    $e
                );
            }
            throw new RuntimeException('Failed to create user: ' . $e->getMessage(), 0, $e);
        }
    }

    // =========================================================
    //  READ — single record
    // =========================================================

    /** Find by surrogate primary key. */
    public function findById(int $id, bool $includeDeleted = false): ?UserDto
    {
        $sql  = 'SELECT * FROM users WHERE id = :id' . $this->deletedClause($includeDeleted);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch();

        return $row !== false ? UserDto::fromRow($row) : null;
    }

    /** Find by the application-facing user_id (login handle). */
    public function findByUserId(string $userId, bool $includeDeleted = false): ?UserDto
    {
        $sql  = 'SELECT * FROM users WHERE user_id = :user_id' . $this->deletedClause($includeDeleted);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        $row  = $stmt->fetch();

        return $row !== false ? UserDto::fromRow($row) : null;
    }

    /** Find by email address (case-insensitive). */
    public function findByEmail(string $email, bool $includeDeleted = false): ?UserDto
    {
        $sql  = 'SELECT * FROM users WHERE LOWER(email) = LOWER(:email)' . $this->deletedClause($includeDeleted);
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => trim($email)]);
        $row  = $stmt->fetch();

        return $row !== false ? UserDto::fromRow($row) : null;
    }

    // =========================================================
    //  READ — collections
    // =========================================================

    /**
     * Return a paginated list of users.
     *
     * @param  array<string,string> $filters  Supported keys: role, is_active
     * @return UserDto[]
     */
    public function findAll(
        int    $limit          = 50,
        int    $offset         = 0,
        array  $filters        = [],
        string $orderBy        = 'created_at',
        string $direction      = 'DESC',
        bool   $includeDeleted = false,
    ): array {
        $allowedColumns    = ['id','user_id','email','first_name','last_name','role','created_at','updated_at'];
        $allowedDirections = ['ASC', 'DESC'];

        if (!in_array($orderBy, $allowedColumns, true)) {
            $orderBy = 'created_at';
        }
        if (!in_array(strtoupper($direction), $allowedDirections, true)) {
            $direction = 'DESC';
        }

        $where  = [];
        $params = [];

        if (!$includeDeleted) {
            $where[] = 'deleted_at IS NULL';
        }

        if (isset($filters['role'])) {
            $where[]          = 'role = :role';
            $params[':role']  = $filters['role'];
        }

        if (isset($filters['is_active'])) {
            $where[]             = 'is_active = :is_active';
            $params[':is_active'] = $filters['is_active'] ? 'true' : 'false';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = <<<SQL
            SELECT * FROM users
            {$whereClause}
            ORDER BY {$orderBy} {$direction}
            LIMIT :limit OFFSET :offset
        SQL;

        $stmt = $this->pdo->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return array_map(
            fn(array $row) => UserDto::fromRow($row),
            $stmt->fetchAll()
        );
    }

    /** Count total matching rows (for pagination metadata). */
    public function count(array $filters = [], bool $includeDeleted = false): int
    {
        $where  = [];
        $params = [];

        if (!$includeDeleted) {
            $where[] = 'deleted_at IS NULL';
        }
        if (isset($filters['role'])) {
            $where[]         = 'role = :role';
            $params[':role'] = $filters['role'];
        }
        if (isset($filters['is_active'])) {
            $where[]              = 'is_active = :is_active';
            $params[':is_active'] = $filters['is_active'] ? 'true' : 'false';
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $stmt        = $this->pdo->prepare("SELECT COUNT(*) FROM users {$whereClause}");
        $stmt->execute($params);

        return (int) $stmt->fetchColumn();
    }

    // =========================================================
    //  UPDATE
    // =========================================================

    /**
     * Apply a partial update to a user identified by surrogate id.
     * Returns the refreshed DTO, or null if the user was not found.
     *
     * @throws RuntimeException on duplicate email or DB error.
     */
    public function update(int $id, UpdateUserInput $input): ?UserDto
    {
        $changeset = $input->toChangeset();

        if (empty($changeset)) {
            // Nothing to change — just return the current record.
            return $this->findById($id);
        }

        $setClauses = [];
        $params     = [':id' => $id];

        foreach ($changeset as $column => $value) {
            $placeholder        = ':' . $column;
            $setClauses[]       = "{$column} = {$placeholder}";
            $params[$placeholder] = $value;
        }

        $setString = implode(', ', $setClauses);

        $sql = <<<SQL
            UPDATE users
            SET {$setString}
            WHERE id = :id
              AND deleted_at IS NULL
            RETURNING *
        SQL;

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            $row  = $stmt->fetch();

            return $row !== false ? UserDto::fromRow($row) : null;

        } catch (PDOException $e) {
            if (str_contains($e->getMessage(), '23505')) {
                throw new RuntimeException(
                    'A user with that email already exists.',
                    409,
                    $e
                );
            }
            throw new RuntimeException('Failed to update user: ' . $e->getMessage(), 0, $e);
        }
    }

    // =========================================================
    //  PASSWORD
    // =========================================================

    /** Replace the password hash for a given user id. */
    public function updatePassword(int $id, string $plainPassword): bool
    {
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters.');
        }

        $hash = password_hash($plainPassword, PASSWORD_ARGON2ID);

        $stmt = $this->pdo->prepare(
            'UPDATE users SET password_hash = :hash WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':hash' => $hash, ':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Verify a plain-text password against the stored hash. */
    public function verifyPassword(int $id, string $plainPassword): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT password_hash FROM users WHERE id = :id AND deleted_at IS NULL AND is_active = true'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        if ($row === false) {
            return false;
        }

        return password_verify($plainPassword, $row['password_hash']);
    }

    // =========================================================
    //  SOFT DELETE
    // =========================================================

    /**
     * Soft-delete: sets deleted_at to NOW().
     * Returns true if a row was affected.
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET deleted_at = NOW(), is_active = false WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    /** Restore a soft-deleted user. */
    public function restore(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE users SET deleted_at = NULL, is_active = true WHERE id = :id AND deleted_at IS NOT NULL'
        );
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // =========================================================
    //  HARD DELETE
    // =========================================================

    /**
     * Permanently remove a user row.
     * Use with caution — prefer softDelete() in most cases.
     */
    public function hardDelete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // =========================================================
    //  AUTH HELPERS
    // =========================================================

    /** Record a successful login: clear fail-count, update last_login_at. */
    public function recordSuccessfulLogin(int $id): void
    {
        $this->pdo->prepare(
            'UPDATE users SET last_login_at = NOW(), failed_login_count = 0, locked_until = NULL WHERE id = :id'
        )->execute([':id' => $id]);
    }

    /**
     * Increment failed login counter.
     * Automatically locks the account for $lockMinutes after $maxAttempts failures.
     */
    public function recordFailedLogin(int $id, int $maxAttempts = 5, int $lockMinutes = 15): void
    {
        $this->pdo->prepare(
            'UPDATE users SET failed_login_count = failed_login_count + 1 WHERE id = :id'
        )->execute([':id' => $id]);

        $stmt = $this->pdo->prepare('SELECT failed_login_count FROM users WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $count = (int) $stmt->fetchColumn();

        if ($count >= $maxAttempts) {
            $this->pdo->prepare(
                "UPDATE users SET locked_until = NOW() + INTERVAL '{$lockMinutes} minutes' WHERE id = :id"
            )->execute([':id' => $id]);
        }
    }

    /** Check whether a user account is currently locked. */
    public function isLocked(int $id): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT locked_until FROM users WHERE id = :id AND deleted_at IS NULL'
        );
        $stmt->execute([':id' => $id]);
        $lockedUntil = $stmt->fetchColumn();

        if (!$lockedUntil) {
            return false;
        }

        return new \DateTimeImmutable($lockedUntil) > new \DateTimeImmutable();
    }

    // =========================================================
    //  HELPERS
    // =========================================================

    private function deletedClause(bool $includeDeleted): string
    {
        return $includeDeleted ? '' : ' AND deleted_at IS NULL';
    }
}
