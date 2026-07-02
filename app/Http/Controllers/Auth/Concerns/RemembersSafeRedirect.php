<?php

namespace App\Http\Controllers\Auth\Concerns;

use Illuminate\Http\Request;

trait RemembersSafeRedirect
{
    protected function rememberSafeRedirect(Request $request): void
    {
        $redirect = $request->query('redirect');

        if (! is_string($redirect) || trim($redirect) === '') {
            return;
        }

        if (! str_starts_with($redirect, url('/'))) {
            return;
        }

        $request->session()->put('url.intended', $redirect);
    }
}
