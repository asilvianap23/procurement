<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
	public function handle(Request $request, Closure $next)
	{
		if (Auth::check() && Auth::user()->hasRole('admin')) {
			return $next($request);
		}
		
		return redirect('/welcome')->with('error', 'Anda tidak memiliki akses ke halaman ini.');
	}
}
