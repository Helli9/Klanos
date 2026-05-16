<?php
namespace App\Models;
use Config\Database;

class LoginAttemptModel {

    private const MAX_EMAIL_ATTEMPTS = 5;
    private const MAX_IP_ATTEMPTS = 50;
    private const LOCKOUT_MINUTES = 15;

    public static function record(string $email, string $ip): void 
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO login_attempts (email, ip) VALUES (?, ?)");
        $stmt->execute([$email, $ip]);
    }

    public static function isLocked(string $email, string $ip): bool 
    {
        $pdo = Database::getInstance();

        $stmtEmail = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE email = ? AND attempted_at > NOW() - INTERVAL ? MINUTE
        ");
        $stmtEmail->execute([$email, self::LOCKOUT_MINUTES]);
        if((int) $stmtEmail->fetchColumn() >= self::MAX_EMAIL_ATTEMPTS){
            return true;
        }

        $stmtIp = $pdo->prepare("
            SELECT COUNT(*) FROM login_attempts
            WHERE ip = ? AND attempted_at > NOW() - INTERVAL ? MINUTE
        ");
        $stmtIp->execute([$ip, self::LOCKOUT_MINUTES]);
        if((int)  $stmtIp->fetchColumn() >= self::MAX_IP_ATTEMPTS){
            return true;
        }

        return false;
        
    }

    public static function clearFor(string $email): void 
    {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM login_attempts WHERE email = ?");
        $stmt->execute([$email]);
    }
}