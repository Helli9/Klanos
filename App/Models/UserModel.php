<?php
namespace App\Models;
use Config\Database;

class UserModel {

    public static function findByEmail(string $email): mixed {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function signin(string $email): mixed {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, name, password FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public static function create(string $name, string $email, string $hashedPassword): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
        return $stmt->execute([$name, $email, $hashedPassword]);
    }

    public static function getClass(int $user_id): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT player_class FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch() ?: [];
    }
}