<?php
namespace App\Models;
use Config\Database;

class NeedListModel {

    public static function create(string $category, string $item, string $type, int $user_id): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO need_list (category, item, type, user_id) VALUES (?, ?, ?, ?)");
        return $stmt->execute([$category, $item, $type, $user_id]);
    }

    public static function getPvp(int $user_id): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT category, item FROM need_list WHERE type = 'pvp' AND user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public static function getPve(int $user_id): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT category, item FROM need_list WHERE type = 'pve' AND user_id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetchAll();
    }

    public static function delete(string $category, string $item, int $user_id): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("DELETE FROM need_list WHERE category = ? AND item = ? AND user_id = ?");
        return $stmt->execute([$category, $item, $user_id]);
    }
}