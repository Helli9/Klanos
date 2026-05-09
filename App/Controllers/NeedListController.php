<?php
namespace App\Controllers;

use App\Models\NeedListModel;
use App\Core\Controller;

class NeedListController  extends Controller
{ 

    public function create(){
         $this->requireAuth();

        $errors = [];
        $user_id = (int) $_SESSION['user_id'];
        $category = trim($_POST['category'] ?? '');
        $item = trim($_POST['item'] ?? '');
        $mode = trim($_POST['mode'] ?? '');
        $type = ($mode === 'pvp') ? 'pvp' : 'pve';

        // 3. BASIC VALIDATION
        if(empty($category)) $errors['category'] = "Please select a category";  
        if(empty($item)) $errors['item'] = "Please select an item";

            
        if (empty($errors)){
            NeedListModel::create($category, $item, $type, $user_id);
            $this->redirect('/home?tab=need_lists');
        }
        
        $this->view('layout/home', ['errors' => $errors, 'tab'    => 'need_lists',]);
    }    

    public function delete(){
        $this->requireAuth();

        $errors   = [];
        $user_id  = (int) $_SESSION['user_id'];
        $category = trim($_POST['category'] ?? '');
        $item     = trim($_POST['item'] ?? '');
                
        // 3. BASIC VALIDATION
        if(empty($category)) $errors['category'] = "Please select a category";  
        if(empty($item)) $errors['item'] = "Please select an item";
        
        if (empty($errors)){
            NeedListModel::delete($category, $item, $user_id);
            $this->redirect('/home?tab=need_lists');
        }
        
        $this->view('layout/home', ['errors' => $errors, 'tab'    => 'need_lists',]);
    }    
}
?>