<?php
namespace App\Controllers;


use App\Core\Controller;
use App\Validators\NeedListValidators;
use  App\Services\NeedService;

class NeedListController  extends Controller
{ 
    public function __construct(private NeedService $needService) {}

    public function create(){
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->redirect('/home?tab=need_lists');


        ['user_id' => $user_id, 'category' => $category, 'item' => $item]
            = $this->getNeedListInput();
        $type   = $this->resolveType(trim($_POST['mode'] ?? ''));

        $errors = NeedListValidators::validateNeedList($category, $item);
        if (empty($errors)) {
            $result = $this->needService->create($category, $item, $type, $user_id);
            if (isset($result['error'])) {
                $errors['generic'] = $result['error'];
            } else {
                $this->redirect('/home?tab=need_lists');
            }
        }
        $this->renderNeedListErrors($errors);
    }    

    public function delete(){
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST')
            $this->redirect('/home?tab=need_lists');


        ['user_id' => $user_id, 'category' => $category, 'item' => $item]
            = $this->getNeedListInput();

        $errors = NeedListValidators::validateNeedList($category, $item);
        if (empty($errors)) {
            $result = $this->needService->delete($category, $item, $user_id);
            if (isset($result['error'])) {
                $errors['generic'] = $result['error'];
            } else {
                $this->redirect('/home?tab=need_lists');
            }
        }
        $this->renderNeedListErrors($errors);
    }    


    private function resolveType(string $mode): string
    {
        return $mode === 'pvp' ? 'pvp' : 'pve';
    }

    private function getNeedListInput(): array
    {
        return [
            'user_id'  => (int) $_SESSION['user_id'],
            'category' => trim($_POST['category'] ?? ''),
            'item'     => trim($_POST['item'] ?? ''),
        ];
    }
    private function renderNeedListErrors(array $errors): void
    {
        $this->view('layout/home', [
            'errors' => $errors,
            'tab' => 'need_lists',
        ]);
    }
}
?>