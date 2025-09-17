<?php

namespace Tests;
require_once __DIR__ . '/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use App\Services\DbService;
use App\Services\UserService;
use App\Models\User;

class UserTest extends TestCase
{
    private $userService;
    protected function setUp(): void
    {
        $dbService = new DbService();
        $pdo = $dbService->getConnection();
        $this->userService = new UserService($pdo);

        //clean table before each test
        $this->userService->truncateUsers();
    }
    public function testCreateUser()
    {
        $user = new User(
            name: 'Anakin Skywalker',
            cpfCnpj: '12345678901',
            email: 'anakin@example.com',
            password: 'padme',
            type: 'common',
            balance: 100.00
        );

        $createdUser = $this->userService->createUser($user);

        $this->assertInstanceOf(User::class, $createdUser);
        $this->assertEquals('Anakin Skywalker', $createdUser->getName());
        $this->assertEquals(100.00, $createdUser->getBalance());

        $this->assertNotNull($createdUser->getCreatedAt());
        $this->assertNotNull($createdUser->getUpdatedAt());

        $this->assertInstanceOf(\DateTimeImmutable::class, $createdUser->getCreatedAt());
        $this->assertInstanceOf(\DateTimeImmutable::class, $createdUser->getUpdatedAt());

        $authenticated = $this->userService->authenticate('anakin@example.com', 'padme');
        $this->assertEquals($createdUser->getId(), $authenticated->getId());
    }
}