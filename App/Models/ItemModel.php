<?php
namespace App\Models;
use Config\Database;

class ItemModel {

    public function getByCategory(string $category): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, item FROM items WHERE category = ?");
        $stmt->execute([$category]);
        return $stmt->fetchAll();
    }
}