<?php 
namespace App\Requests;

use App\Core\FormRequest;

class EventsRegister extends FormRequest
{
    protected function validate(): void
    {
         $id = $this->input('event_id');

        if (empty($id) || !ctype_digit((string) $id)) {
            $this->errors['event_id'] = 'Invalid event.';
        }
    }
    

    public function event_id(): string
    {
        return trim($this->input('event_id'));
    }


    public function user_id(): int
    {
        return (int) $_SESSION['user_id'];
    }


    public function status(): string
    {
        $type  = trim($_POST['mode'] ?? '');
        return $type === 'pvp' ? 'pvp' : 'pve';
    }
}
