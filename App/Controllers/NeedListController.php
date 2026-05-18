<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NeedService;
use App\Requests\DeleteNeedRequest;
use App\Requests\CreateNeedRequest;

class NeedListController  extends Controller 
{ 
    public function __construct(private NeedService $needService) {}

    public function create()
    {
        // 1. Initialize and Validate
        $request = new CreateNeedRequest($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', [
                'errors' => $request->errors(),
                'old'    => $request->all() // Good for repopulating fields
            ]);
        }

        try {
            $this->needService->create(
                $request->category(), 
                $request->item(), 
                $request->mode(), 
                $request->user()
            );
            // 4. Success

            return $this->redirect('/home?tab=need_lists');

        } catch (\RuntimeException $e) {
            return $this->view('pages/login', ['errors' => ['generic' => $e->getMessage()]]);
        }
    }    

    public function delete()
    {
        // 1. Initialize and Validate
        $request = new DeleteNeedRequest($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', [
                'errors' => $request->errors(),
                'old'    => $request->all() // Good for repopulating fields
            ]);
        }

         try {
             $this->needService->delete(
                $request->id(),
                $request->user()
            );
            // 4. Success

            return $this->redirect('/home?tab=need_lists');

        } catch (\RuntimeException $e) {
            return $this->view('pages/login', ['errors' => ['generic' => $e->getMessage()]]);
        }
    }    
}
?>