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
        $sessionLocale = $request->session()->get('locale');

        if (is_string($sessionLocale) && Locale::isSupported($sessionLocale)) {
            return $sessionLocale;
        }

        $appLocale = config('app.locale', 'fa');

        $user = $request->user();

        if ($user) {
            if ($user->locale === 'en' && $appLocale === 'fa') {
                $user->forceFill(['locale' => 'fa'])->saveQuietly();

                return 'fa';
            }

            if (Locale::isSupported($user->locale)) {
                return $user->locale;
            }

            return Locale::isSupported($appLocale) ? $appLocale : 'en';
        }

        return Locale::isSupported($appLocale) ? $appLocale : 'en';
    }
}
