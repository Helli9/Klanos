<?php 
namespace App\Requests;

use App\Core\FormRequest;

class CreateNeedRequest extends FormRequest
{
    protected function validate(): void
    {
        $item = $this->input('item');
        $category = $this->input('category');        

        if (empty($category)) {
            $this->errors['category'] = "Please select a category";
        } 
        if(empty($item)) {
            $this->errors['item'] = "Please select an item";
        }
    }

    public function category(): string
    {
        return trim($this->input('category'));
    }

    public function item(): string
    {
        return trim($this->input('item'));
    }

    public function user(): int
    {
        return (int) $_SESSION['user_id'];
    }


    public function mode(): string
    {
        $type  = trim($_POST['mode'] ?? '');
        return $type === 'pvp' ? 'pvp' : 'pve';
    }
}
