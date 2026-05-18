<?php
namespace App\Services;
use App\Models\EventsModel;


class EventsService 
{
    public function __construct(private EventsModel $event){}

    public function register(int $event_id, int $user_id, string $status): void 
    {

        if (EventsModel::hasUserRegistered($event_id, $user_id)) 
            throw new \RuntimeException('You are already registered for this event.');
        
        //$created = EventsModel::register($event_id, $user_id, $status);
        $created = $this->event->register($event_id, $user_id, $status);

        if (!$created)
            throw new \RuntimeException('Something went wrong. Please try again.');
    }
}
