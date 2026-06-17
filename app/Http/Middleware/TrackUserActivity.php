<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Models\UserActivityLog;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackUserActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            static::recordForUser($user);
        }

        return $next($request);
    }

    public static function recordForUser(User $user): void
    {
        $today = Carbon::today();
        $cacheKey = "activity-tracked-{$user->id}-{$today->toDateString()}";

        if (Cache::has($cacheKey)) {
            return;
        }

        $log = UserActivityLog::firstOrNew([
            'user_id' => $user->id,
            'date' => $today,
        ]);

        $log->visits_count = ($log->exists ? $log->visits_count : 0) + 1;
        $log->save();

        Cache::put(
            $cacheKey,
            true,
            $today->copy()->endOfDay()
        );
    }
}
