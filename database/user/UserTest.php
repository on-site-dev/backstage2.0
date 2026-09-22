<?php

declare(strict_types=1);

namespace bs20php\database\user;

use Backstage\User\CreateUserInput;
use Backstage\User\UpdateUserInput;
use Backstage\User\UserDto;
use Backstage\User\UserRepository;
use Backstage\User\UserService;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Unit tests for UserRepository and UserService using a PDO mock.
 *
 * Run:  ./vendor/bin/phpunit tests/
 */
class UserRepositoryTest extends TestCase
{
    private PDOMockObject         $pdo;
    private PDOStatementMockObject $stmt;
    private UserRepository          $repo;

    protected function setUp(): void
    {
        $this->pdo  = $this->createMock(PDO::class);
        $this->stmt = $this->createMock(PDOStatement::class);
        $this->repo = new UserRepository($this->pdo);
    }

    // ------------------------------------------------------------------
    //  Helpers
    // ------------------------------------------------------------------

    private function sampleRow(): array
    {
        return [
            'id'                  => 1,
            'user_id'             => 'jsmith',
            'email'               => 'jane@example.com',
            'password_hash'       => password_hash('secret123', PASSWORD_ARGON2ID),
            'first_name'          => 'Jane',
            'last_name'           => 'Smith',
            'display_name'        => 'Jane Smith',
            'role'                => 'user',
            'is_active'           => true,
            'is_email_verified'   => false,
            'avatar_url'          => null,
            'last_login_at'       => null,
            'failed_login_count'  => 0,
            'locked_until'        => null,
            'created_at'          => '2025-01-01 00:00:00+00',
            'updated_at'          => '2025-01-01 00:00:00+00',
            'deleted_at'          => null,
        ];
    }

    // ------------------------------------------------------------------
    //  findById
    // ------------------------------------------------------------------

    public function testFindByIdReturnsDto(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn($this->sampleRow());

        $user = $this->repo->findById(1);

        $this->assertInstanceOf(UserDto::class, $user);
        $this->assertSame(1, $user->id);
        $this->assertSame('jsmith', $user->userId);
        $this->assertSame('Jane Smith', $user->displayName);
    }

    public function testFindByIdReturnsNullWhenNotFound(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repo->findById(999));
    }

    // ------------------------------------------------------------------
    //  findByEmail
    // ------------------------------------------------------------------

    public function testFindByEmailReturnsDto(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('fetch')->willReturn($this->sampleRow());

        $user = $this->repo->findByEmail('jane@example.com');

        $this->assertNotNull($user);
        $this->assertSame('jane@example.com', $user->email);
    }

    // ------------------------------------------------------------------
    //  softDelete
    // ------------------------------------------------------------------

    public function testSoftDeleteReturnsTrueOnSuccess(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(1);

        $this->assertTrue($this->repo->softDelete(1));
    }

    public function testSoftDeleteReturnsFalseWhenNotFound(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('execute')->willReturn(true);
        $this->stmt->method('rowCount')->willReturn(0);

        $this->assertFalse($this->repo->softDelete(999));
    }

    // ------------------------------------------------------------------
    //  UpdateUserInput — toChangeset
    // ------------------------------------------------------------------

    public function testUpdateInputOnlyIncludesNonNullFields(): void
    {
        $input     = new UpdateUserInput(email: 'new@example.com');
        $changeset = $input->toChangeset();

        $this->assertArrayHasKey('email', $changeset);
        $this->assertArrayNotHasKey('first_name', $changeset);
        $this->assertArrayNotHasKey('role', $changeset);
    }

    // ------------------------------------------------------------------
    //  CreateUserInput — password hashing
    // ------------------------------------------------------------------

    public function testCreateUserInputHashesPassword(): void
    {
        $input = new CreateUserInput(
            userId:        'tuser',
            email:         'test@example.com',
            plainPassword: 'ValidPass1!',
        );

        $this->assertTrue(password_verify('ValidPass1!', $input->passwordHash));
    }

    public function testCreateUserInputRejectsShortPassword(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateUserInput(
            userId:        'tuser',
            email:         'test@example.com',
            plainPassword: 'short',
        );
    }

    public function testCreateUserInputRejectsInvalidEmail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new CreateUserInput(
            userId:        'tuser',
            email:         'not-an-email',
            plainPassword: 'ValidPass1!',
        );
    }
}


// ------------------------------------------------------------------
//  UserService unit tests
// ------------------------------------------------------------------

class UserServiceTest extends TestCase
{
    private UserRepositoryMockObject $repo;
    private UserService               $service;

    protected function setUp(): void
    {
        $this->repo    = $this->createMock(UserRepository::class);
        $this->service = new UserService($this->repo);
    }

    private function dto(array $overrides = []): UserDto
    {
        $row = array_merge([
            'id'                 => 1,
            'user_id'            => 'jsmith',
            'email'              => 'jane@example.com',
            'password_hash'      => password_hash('secret', PASSWORD_ARGON2ID),
            'first_name'         => 'Jane',
            'last_name'          => 'Smith',
            'display_name'       => 'Jane Smith',
            'role'               => 'user',
            'is_active'          => true,
            'is_email_verified'  => false,
            'avatar_url'         => null,
            'last_login_at'      => null,
            'failed_login_count' => 0,
            'locked_until'       => null,
            'created_at'         => '2025-01-01 00:00:00+00',
            'updated_at'         => '2025-01-01 00:00:00+00',
            'deleted_at'         => null,
        ], $overrides);

        return UserDto::fromRow($row);
    }

    public function testGetByIdThrowsWhenNotFound(): void
    {
        $this->repo->method('findById')->willReturn(null);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(404);
        $this->service->getById(999);
    }

    public function testRegisterThrowsOnDuplicateEmail(): void
    {
        $this->repo->method('findByEmail')->willReturn($this->dto());
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(409);
        $this->service->register('other', 'jane@example.com', 'Password1!');
    }

    public function testDeactivateThrowsWhenNotFound(): void
    {
        $this->repo->method('softDelete')->willReturn(false);
        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(404);
        $this->service->deactivate(999);
    }

    public function testAuthenticateThrowsOnBadPassword(): void
    {
        $this->repo->method('findByUserId')->willReturn($this->dto());
        $this->repo->method('isLocked')->willReturn(false);
        $this->repo->method('verifyPassword')->willReturn(false);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(401);
        $this->service->authenticate('jsmith', 'wrongpassword');
    }

    public function testAuthenticateThrowsOnLockedAccount(): void
    {
        $this->repo->method('findByUserId')->willReturn($this->dto());
        $this->repo->method('isLocked')->willReturn(true);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionCode(423);
        $this->service->authenticate('jsmith', 'anypassword');
    }

    public function testPaginateReturnsPaginationMeta(): void
    {
        $this->repo->method('findAll')->willReturn([$this->dto()]);
        $this->repo->method('count')->willReturn(1);

        $result = $this->service->paginate(page: 1, perPage: 10);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertSame(1, $result['total']);
        $this->assertSame(1, $result['page']);
    }
}
