<?php

namespace App\Http\Middleware;

use Closure;

class Languages
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
//        dd(session()->get('language'));
        if (session()->has('language')) {
            $lang = session()->get('language');
            app()->setLocale($lang);
        } else {
            session()->put('language', 'en');
            app()->setLocale('en');
        }
        return $next($request);
    }
}
