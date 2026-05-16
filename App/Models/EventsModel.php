<?php
namespace App\Models;
use Config\Database;

class EventsModel {

    public static function register(int $event_id, int $user_id, string $status): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO event_attendees (event_id, user_id, type, status) VALUES (?, ?, ?)");
        return $stmt->execute([$event_id, $user_id, $status]);
    }

    public static function getEvents(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, title, event_date FROM events");
        $stmt->execute();
        return $stmt->fetchAll();
    }

}