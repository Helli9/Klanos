<?php
namespace App\Core;

abstract class FormRequest
{
    protected array $data;
    protected array $errors = [];

    public function __construct(array $data)
    {
        $this->data = $this->sanitize($data);
        $this->validate();
    }

    private function sanitize(array $data): array
    {
        foreach ($data as $key => $value) {
            $data[$key] = is_string($value)
                ? trim($value)
                : $value;
        }
        return $data;
    }

    abstract protected function validate(): void;

    public function isValid(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function input(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function all(): array
    {
        return $this->data;
    }
}