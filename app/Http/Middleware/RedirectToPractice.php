<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectToPractice
{
    public function handle(Request $request, Closure $next): Response
    {
        // Only redirect if a practice is active in this session
        $slug  = (string) session('practice_slug', '');
        $token = (string) session('practice_token', '');

        if ($slug === '') {
            return $next($request);
        }

        // Normalize request path (no leading slash) and keep query string
        $path = ltrim($request->path(), '/');
        $qs = $request->getQueryString();
        $q  = $qs ? ('?'.$qs) : '';

        // ---- OLD LANDING PAGES ----
        if ($path === 'landing' || $path === 'landing/') {
            return redirect("/practice/{$slug}{$q}");
        }
        if (preg_match('#^landing/(companies|clients|tasks|users|deadlines)$#', $path, $m)) {
            $section = $m[1];
            if ($section === 'companies') {
                return redirect("/practice/{$slug}/companies{$q}");
            }
            if ($section === 'users') {
                return redirect($token ? "/lead/add-users?t={$token}{$q}" : "/practice/{$slug}{$q}");
            }
            // clients / tasks / deadlines → practice home (until those pages exist)
            return redirect("/practice/{$slug}{$q}");
        }

        // ---- SHORT TOP-LEVEL LINKS USED BY THE OLD SIDEBAR ----
        if ($path === 'companies') {
            return redirect("/practice/{$slug}/companies{$q}");
        }
        if ($path === 'users') {
            return redirect($token ? "/lead/add-users?t={$token}{$q}" : "/practice/{$slug}{$q}");
        }
        if (in_array($path, ['clients','tasks','deadlines'], true)) {
            return redirect("/practice/{$slug}{$q}");
        }

        return $next($request);
    }
}
