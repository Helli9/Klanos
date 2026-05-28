<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\EventsService;
use App\Services\HomeService;
use App\Requests\EventsRegister;
use App\Security\CsrfGuard;



class EventsController  extends Controller 
{ 
    public function __construct(
        private EventsService $eventsService,
        private HomeService $homeService,
        private CsrfGuard $csrfGuard
    ){}

    public function register()
    {
        // 1. Initialize and Validate
        $request = new EventsRegister($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', array_merge(
                $this->homeService->getHomeData($request->user_id(), null),
                [
                    'errors' => $request->errors(),
                    'old'    => $request->all(),
                    'csrfToken' => $this->csrfGuard->get()
                ]
            ));
        }

        try {
            $this->eventsService->register(
                $request->event_id(), 
                $request->user_id(), 
                $request->status(), 
            );
            // 4. Success
            $_SESSION['success'] = 'You have successfully registered for the event.';
            return $this->redirect('/home?tab=dashboard');

        } catch (\RuntimeException $e) {
             return $this->view('layout/home', array_merge(
                $this->homeService->getHomeData($request->user_id(), null),
                [
                    'errors' => ['generic' => $e->getMessage()],
                    'old'    => $request->all(),
                    'csrfToken' => $this->csrfGuard->get(),
                ]
            ));
        }
    }    
}
?>