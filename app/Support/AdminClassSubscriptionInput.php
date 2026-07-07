<?php

namespace App\Support;

use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminClassSubscriptionInput
{
    public static function merge(array $data, Request $request, ?SchoolClass $class = null): array
    {
        if (! $request->filled('subscription_ends_at')) {
            $data['subscription_ends_at'] = null;
            $data['subscription_revoked_at'] = null;

            return $data;
        }

        $newEnd = Carbon::parse($request->input('subscription_ends_at'))->endOfDay();
        $data['subscription_ends_at'] = $newEnd;

        if ($class?->subscription_revoked_at) {
            if (! $class->subscription_ends_at || $newEnd->gt($class->subscription_ends_at)) {
                $data['subscription_revoked_at'] = null;
            }
        }

        return $data;
    }
}
