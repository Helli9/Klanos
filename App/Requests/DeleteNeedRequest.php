<?php 
namespace App\Requests;

use App\Core\FormRequest;

class DeleteNeedRequest extends FormRequest
{
    protected function validate(): void
    {
        $id = $this->input('need_id');

        if (empty($id) || !ctype_digit((string) $id)) {
            $this->errors['need_id'] = 'Invalid item.';
        }
    }


    public function id(): int
    {
        return (int) $this->input('need_id');
    }


    public function user(): string
    {
        return (int) $_SESSION['user_id'];
    }
}
