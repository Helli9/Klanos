<?php
namespace App\Services;
use App\Models\EventsModel;


class EventsService 
{
    public function register(int $event_id, int $user_id, string $status): array {
        $created = EventsModel::register($event_id, $user_id, $status);

        return $created
            ? ['success' => true]
            : ['error' => 'Something went wrong. Please try again.'];
    }

}
