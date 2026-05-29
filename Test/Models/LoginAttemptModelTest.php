<?php
use PHPUnit\Framework\TestCase;
use App\Models\LoginAttemptModel;
use Config\Database;

class LoginAttemptModelTest extends TestCase 
{
    private $pdo;
    private $loginAttemptModel;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pdo = Database::getInstance();
        
        // Setup isolated test database environment
        $this->pdo->exec("CREATE DATABASE IF NOT EXISTS my_project_test");
        $this->pdo->exec("USE my_project_test");

        // Create the login_attempts schema
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS login_attempts (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            email VARCHAR(255) NOT NULL,
            ip VARCHAR(45) NOT NULL,
            attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        $this->loginAttemptModel = new LoginAttemptModel(); 
    }

    protected function tearDown(): void
    {
        // Clear out the data after every single test run
        $this->pdo->exec("DROP TABLE IF EXISTS login_attempts");
        parent::tearDown();
    }

    // --- TEST: record ---

    public function test_record_logs_attempt_with_current_timestamp(): void
    {
        $email = "user@example.com";
        $ip = "192.168.1.1";

        $this->loginAttemptModel->record($email, $ip);

        $stmt = $this->pdo->prepare("SELECT email, ip, attempted_at FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotEmpty($row);
        $this->assertEquals($email, $row['email']);
        $this->assertEquals($ip, $row['ip']);
        // Verify a timestamp was generated within the last few seconds
        $this->assertGreaterThan(time() - 5, strtotime($row['attempted_at']));
    }

    // --- TEST: isLocked (Email Threshold) ---

    public function test_is_locked_returns_true_if_email_reaches_max_attempts(): void
    {
        $email = "target@example.com";
        $ip = "127.0.0.1";

        // MAX_EMAIL_ATTEMPTS is 5. Let's record 4 (should stay unlocked)
        for ($i = 0; $i < 4; $i++) {
            $this->loginAttemptModel->record($email, $ip);
        }
        $this->assertFalse($this->loginAttemptModel->isLocked($email, $ip));

        // Record the 5th attempt (hits threshold -> locks)
        $this->loginAttemptModel->record($email, $ip);
        $this->assertTrue($this->loginAttemptModel->isLocked($email, $ip));
    }

    // --- TEST: isLocked (IP Threshold) ---

    public function test_is_locked_returns_true_if_ip_reaches_max_attempts(): void
    {
        $ip = "203.0.113.50";

        // MAX_IP_ATTEMPTS is 50. Let's quickly seed 50 different emails from this IP
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, ip) VALUES (?, ?)");
        for ($i = 0; $i < 50; $i++) {
            $stmt->execute(["user{$i}@example.com", $ip]);
        }

        // Check if an unrelated clean email is blocked because of the bad IP address
        $this->assertTrue($this->loginAttemptModel->isLocked("innocent@example.com", $ip));
    }

    // --- TEST: isLocked (Time Window Expiry) ---

    public function test_is_locked_ignores_attempts_older_than_lockout_minutes(): void
    {
        $email = "old-attempts@example.com";
        $ip = "10.0.0.1";

        // Manually inject 5 failed attempts that happened 16 minutes ago (Outside the 15-minute window)
        $pastTimestamp = date('Y-m-d H:i:s', strtotime('-16 minutes'));
        $stmt = $this->pdo->prepare("INSERT INTO login_attempts (email, ip, attempted_at) VALUES (?, ?, ?)");
        
        for ($i = 0; $i < 5; $i++) {
            $stmt->execute([$email, $ip, $pastTimestamp]);
        }

        // Even though there are 5 attempts, they are stale, so the user should NOT be locked out
        $this->assertFalse($this->loginAttemptModel->isLocked($email, $ip));
    }

    // --- TEST: clearFor ---

    public function test_clear_for_removes_all_attempts_associated_with_email(): void
    {
        $email = "clear-me@example.com";
        $otherEmail = "keep-me@example.com";
        $ip = "192.168.1.1";

        $this->loginAttemptModel->record($email, $ip);
        $this->loginAttemptModel->record($email, $ip);
        $this->loginAttemptModel->record($otherEmail, $ip);

        // Execute target clear method
        $this->loginAttemptModel->clearFor($email);

        // Assert targeted email records are wiped
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
        $this->assertEquals(0, (int)$stmt->fetchColumn());

        // Assert other email records were untouched
        $stmtOther = $this->pdo->prepare("SELECT COUNT(*) FROM login_attempts WHERE email = ?");
        $stmtOther->execute([$otherEmail]);
        $this->assertEquals(1, (int)$stmtOther->fetchColumn());
    }
}