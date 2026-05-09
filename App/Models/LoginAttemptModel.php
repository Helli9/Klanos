<?php
namespace App\Models;
use Config\Database;

class LoginAttemptModel {

    private const MAX_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public static function record(string $email, string $ip): void {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip) VALUES (?, ?)");
        $stmt->execute([$email, $ip]);
    }

    public static function isLocked(string $email, string $ip): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE (email = ? OR ip = ?)
            AND attempted_at > NOW() - INTERVAL ? MINUTE
        ");
        $stmt->execute([$email, $ip, self::LOCKOUT_MINUTES]);
        return (int) $stmt->fetchColumn() >= self::MAX_ATTEMPTS;
    }

    public static function clearFor(string $email): void {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    }
}