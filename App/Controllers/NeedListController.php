<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Services\NeedService;
use App\Requests\DeleteNeedRequest;
use App\Requests\CreateNeedRequest;
use App\Security\CsrfGuard;


class NeedListController  extends Controller 
{ 
    public function __construct(
        private NeedService $needService,
        private CsrfGuard $csrfGuard
    ) {}

    public function create()
    {
        // 1. Initialize and Validate
        $request = new CreateNeedRequest($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', [
                'errors' => $request->errors(),
                'old'    => $request->all(), // Good for repopulating fields
                'csrfToken' => $this->csrfGuard->get()
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
            $_SESSION['success'] = 'Your need list has been saved.';
            return $this->redirect('/home?tab=need_lists');

        } catch (\RuntimeException $e) {
            return $this->view('layout/home', [
                'errors'    => ['generic' => $e->getMessage()],
                'csrfToken' => $this->csrfGuard->get(),
                'old'       => $request->all()
            ]);
        }
    }    

    public function delete()
    {
        // 1. Initialize and Validate
        $request = new DeleteNeedRequest($_POST);

        if (!$request->isValid()) {
            return $this->view('layout/home', [
                'errors' => $request->errors(),
                'old'    => $request->all(),
                'csrfToken' => $this->csrfGuard->get()
            ]);
        }

         try {
             $this->needService->delete(
                $request->id(),
                $request->user()
            );
            // 4. Success
            $_SESSION['success'] = 'Need list deleted successfully.';
            return $this->redirect('/home?tab=need_lists');

        } catch (\RuntimeException $e) {
             return $this->view('layout/home', [
                'errors'    => ['generic' => $e->getMessage()],
                'csrfToken' => $this->csrfGuard->get(),
                'old'       => $request->all()
            ]);
        }
    }    
}
?>