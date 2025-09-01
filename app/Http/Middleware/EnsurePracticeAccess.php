<?php

namespace App\Http\Middleware;

use App\Models\Practice;
use Closure;
use Illuminate\Http\Request;

class EnsurePracticeAccess
{
    /**
     * Allow only owners/members to access a practice workspace.
     * Accepts either a bound Practice model or a slug string for {practice}.
     * Shares $practice with all views.
     */
    public function handle(Request $request, Closure $next)
    {
        // Route param may be a Practice instance (when binding has run)
        // or a string slug (when binding hasn't run yet).
        $param = $request->route('practice');

        if ($param instanceof Practice) {
            $practice = $param;
        } elseif (is_string($param) && $param !== '') {
            $practice = Practice::where('slug', $param)->first();
        } else {
            $practice = null;
        }

        if (! $practice) {
            abort(404);
        }

        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $isOwner  = (int) $practice->owner_id === (int) $user->id;
        $isMember = $practice->members()->where('users.id', $user->id)->exists();

        if (! $isOwner && ! $isMember) {
            abort(403, 'You do not have access to this workspace.');
        }

        // Provide the model to downstream handlers and views
        $request->attributes->set('practice', $practice);
        view()->share('practice', $practice);

        return $next($request);
    }
}
