<?php

namespace App\Http\Middleware;

use App\Support\Locale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        app()->setLocale($locale);
        Carbon::setLocale($locale);

        return $next($request);
    }

    protected function resolveLocale(Request $request): string
    {
        $user = $request->user();

        if ($user && Locale::isSupported($user->locale)) {
            return $user->locale;
        }

        $sessionLocale = $request->session()->get('locale');

        if (is_string($sessionLocale) && Locale::isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        $configured = config('locales.default', config('app.locale', 'en'));

        return Locale::isSupported($configured) ? $configured : 'en';
    }
}
