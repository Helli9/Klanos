<?php
namespace App\Models;
use Config\Database;

class EventsModel {

    public function register(int $event_id, int $user_id, string $status): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("INSERT INTO event_attendees (event_id, user_id, status) VALUES (?, ?, ?)");
        return $stmt->execute([$event_id, $user_id, $status]);
    }

    public function getEvents(): array {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT id, title, event_date FROM events");
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getConfirmedCount(int $eventId): int {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_attendees WHERE event_id = ? AND status = 'confirmed'");
        $stmt->execute([$eventId]);
        return (int) $stmt->fetchColumn();
    }

    public function hasUserRegistered(int $eventId, int $userId): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM event_attendees WHERE event_id = ? AND user_id = ?");
        $stmt->execute([$eventId, $userId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function updateStatus(int $eventId, int $userId, string $status): bool {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare("UPDATE event_attendees SET status = ? WHERE event_id = ? AND user_id = ?");
        return $stmt->execute([$status, $eventId, $userId]);
    }

}