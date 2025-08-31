<?php

namespace App\Http\Middleware;

use App\Models\Practice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePracticeMember
{
    /**
     * Ensure the signed-in user belongs to the {practice:slug} in the URL.
     * Shares $practice with all views so your sidebar can build tenant links.
     */
    public function handle(Request $request, Closure $next): Response
    {
        /** @var \App\Models\Practice|null $practice */
        $practice = $request->route('practice');

        if (! $practice instanceof Practice) {
            abort(404);
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        // Owner is always allowed, otherwise require membership
        $isMember = (int) $practice->owner_id === (int) $user->id
            || $practice->members()->whereKey($user->id)->exists();

        if (! $isMember) {
            abort(403, 'You do not belong to this practice.');
        }

        // Make available to all views (layouts, etc.)
        view()->share('practice', $practice);

        // Remember last tenant (helps when you visit non-tenant pages then come back)
        session(['current_practice_id' => $practice->id]);

        return $next($request);
    }
}
