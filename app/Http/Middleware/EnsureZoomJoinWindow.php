<?php

namespace App\Http\Middleware;

use App\Models\LiveSession;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureZoomJoinWindow
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $liveSession = $request->route('liveSession');

        if (!$liveSession instanceof LiveSession) {
            abort(404, 'Live session not found');
        }

        if (!$liveSession->isWithinTimeWindow()) {
            $windowStart = $liveSession->getTimeWindowStart();
            $windowEnd = $liveSession->getTimeWindowEnd();

            return redirect()->back()
                ->with('error', "Session is only available for joining between {$windowStart->format('Y-m-d H:i')} and {$windowEnd->format('Y-m-d H:i')}");
        }

        return $next($request);
    }
}
