<?php
namespace App\Middleware;

use App\Security\CsrfGuard;


class CsrfMiddleware 
{
    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!CsrfGuard::validate()) {
                http_response_code(419);
                exit('Session expired. Refresh page.');
            }
        }
    }
}
