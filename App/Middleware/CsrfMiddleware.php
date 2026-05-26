<?php
namespace App\Middleware;

use App\Security\CsrfGuard;

class CsrfMiddleware
{
    public function __construct(private CsrfGuard $csrfGuard) {}

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!$this->csrfGuard->validate()) {
                $this->abort();
            }
        }
    }

    protected function abort(): void
    {
        http_response_code(419);
        exit('Session expired. Refresh page.');
    }
}