<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\EventsService;
use App\Requests\EventsRegister;

class EventsController  extends Controller 
{ 
    public function __construct(private EventsService $eventsService) {}

    public function register()
    {
        // 1. Initialize and Validate
        $request = new EventsRegister($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', [
                'errors' => $request->errors(),
                'old'    => $request->all() // Good for repopulating fields
            ]);
        }

        try {
            $this->eventsService->register(
                $request->event_id(), 
                $request->user_id(), 
                $request->status(), 
            );
            // 4. Success
            return $this->redirect('/home?tab=dashboard');

        } catch (\RuntimeException $e) {
            return $this->view('layout/home', ['errors' => ['generic' => $e->getMessage()]]);
        }
    }    
}
?>