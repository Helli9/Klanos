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

        // 2. Attempt Login
        $result = $this->needService->create(
            $request->category(), 
            $request->item(), 
            $request->mode(), 
            $request->user()
        );

        // 3. Handle Service Errors 
        if (isset($result['error'])) {
            return $this->renderNeedListErrors($result);
        }
        return $this->redirect('/home?tab=need_lists');
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

        // 2. Attempt Login
        $result = $this->needService->delete(
            $request->id(),
            $request->user()
        );

        // 3. Handle Service Errors 
        if (isset($result['error'])) {
            return $this->renderNeedListErrors($result);
        }

        return $this->redirect('/home?tab=need_lists');
    }    


    private function renderNeedListErrors(array $result)
    {
        $this->view('layout/home', ['errors' => ['generic' => $result['error']]]);
    }
}
?>