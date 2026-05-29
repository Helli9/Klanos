<?php
use PHPUnit\Framework\TestCase;
use App\Models\UserModel;
use Config\Database;

class UserModelTest  extends TestCase 
{
    private $pdo;
    private $userModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance();
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS my_project_test");
        $this->pdo->exec("USE my_project_test");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            player_class VARCHAR(50) DEFAULT 'Warrior',
            PRIMARY KEY (id)
        )");
        $this->userModel = new UserModel(); 
    }

    protected function tearDown(): void
    {
        $this->pdo->exec("DROP TABLE IF EXISTS users");
        parent::tearDown();
    }

    public function test_create_user_successfully(): void
    {
        $name = "Johsn";
        $email = "johsn@example.com";
        $hashedPassword = password_hash("Secret123", PASSWORD_BCRYPT);

        $result = $this->userModel->create($name, $email, $hashedPassword);

        $this->assertTrue($result);

        $stmt = $this->pdo->query("SELECT * FROM users WHERE email = 'johsn@example.com'");
        $user = $stmt->fetch();

        $this->assertNotFalse($user);
        $this->assertEquals($name, $user['name']);
    }

    public function test_findByEmail_returns_user_when_exists(): void
    {
        $this->pdo->exec("INSERT INTO users (name, email, password) VALUES ('Alice', 'alice@test.com', 'Hash1234')");
        $result = $this->userModel->findByEmail('alice@test.com');
        $this->assertEquals('alice@test.com', $result['email']);
    }

    public function test_findByEmail_returns_false_when_not_found(): void
    {
        $result = $this->userModel->findByEmail('Nodsalice@test.com');
        $this->assertFalse($result);
    }

    public function test_signin_returns_credentials(): void
    {
        $password = password_hash("my_password", PASSWORD_BCRYPT);
        $this->pdo->exec("INSERT INTO users (id, name, email, password) VALUES (42, 'Bob', 'bob@test.com', '$password')");
        $result = $this->userModel->signin('bob@test.com', );

        $this->assertEquals('Bob', $result['name']);
        $this->assertEquals($password, $result['password']);
    }

    public function test_getClass_returns_array_with_user_class(): void
    {
        $this->pdo->exec("INSERT INTO users (id, name, email, password, player_class) VALUES (10, 'MageUser', 'mage@test.com', 'hash', 'Mage')");
        // Act
        $result = $this->userModel->getClass(10);
        $this->assertArrayHasKey('player_class', $result);
        $this->assertEquals('Mage', $result['player_class']);

    }

    public function test_getClass_returns_empty_array_when_user_does_not_exist(): void
    {
        $result = $this->userModel->getClass(1550);
        $this->assertEmpty($result);
    }
}