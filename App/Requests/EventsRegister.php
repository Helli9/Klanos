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

        $mode = trim($this->input('mode', ''));
        $allowed = ['confirmed', 'tentative', 'absent'];
        if (!in_array($mode, $allowed, true)) {
            $this->errors['mode'] = 'Invalid status.';
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
        return match ($type) {
            'confirmed' => 'confirmed',
            'tentative' => 'tentative',
            'absent'    => 'absent',
            default     => 'absent', // Safe fallback if mode is empty or invalid
        };
    }
}
