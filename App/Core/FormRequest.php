<?php
namespace App\Core;

// Abstract It only exists to be copied by other classes.
// Handles sanitization and validation automatically on construction.
// Child classes only need to implement validate() with their own rules.
abstract class FormRequest
{
    // Holds the sanitized input data (trimmed strings)
    protected array $data;

    // Holds validation error messages — empty means valid
    protected array $errors = [];

    // Runs automatically when you do: new LoginRequest([...])
    // 1. Sanitizes the raw input
    // 2. Calls validate() which fills $errors if anything is wrong
    public function __construct(array $data)
    {
        $this->data = $this->sanitize($data);
        $this->validate();
    }

    // Trims whitespace from all string inputs before validation runs
    // e.g. "  hello@test.com  " becomes "hello@test.com"
    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = is_string($value)
                ? trim($value)
                : $value;
        }
        return $data;
    }

    // Every child class MUST implement this with its own rules
    // Fills $this->errors with messages when rules are broken
    abstract protected function validate(): void;

    // Returns true if no errors were found during validate()
    // Called in tests and controllers to check if input is acceptable
    public function isValid(): bool
    {
        return empty($this->errors);
    }

    // Returns the full errors array — keys are field names, values are messages
    public function errors(): array
    {
        // =====> ['email' => 'Email is required', 'password' => 'Too short']
        return $this->errors;
    }

    // Safely reads a single sanitized input value by key
    // Returns $default if the key doesn't exist — avoids undefined index errors
    public function input(string $key, $default = null)
    {
        // =====>  $this->input('email') or $this->input('role', 'user')
        return $this->data[$key] ?? $default;
    }

    // Returns all sanitized input as an array
    public function all(): array
    {
        return $this->data;
    }
}