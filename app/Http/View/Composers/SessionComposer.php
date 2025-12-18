<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class SessionComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $userSessionId = session('user_session_id');
        $apiUrl = $userSessionId ? route('admin.api.session-activities.store') : null;

        $view->with([
            'userSessionId' => $userSessionId,
            'sessionActivityApiUrl' => $apiUrl,
        ]);
    }
}

