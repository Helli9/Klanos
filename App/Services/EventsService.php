<?php
namespace App\Services;
use App\Models\EventsModel;


class EventsService 
{
    public function register(int $event_id, int $user_id, string $status): array {

        if (EventsModel::hasUserRegistered($event_id, $user_id)) {
            return ['error' => 'You are already registered for this event.'];
        }
        $created = EventsModel::register($event_id, $user_id, $status);

        return $created
            ? ['success' => true]
            : ['error' => 'Something went wrong. Please try again.'];
    }
}
