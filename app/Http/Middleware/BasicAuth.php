<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\TraccarUser;

class BasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $AUTH_USER = 'admin';
        $AUTH_PASS = 'admin';
        header('Cache-Control: no-cache, must-revalidate, max-age=0');
        $has_supplied_credentials = !(empty($_SERVER['PHP_AUTH_USER']) && empty($_SERVER['PHP_AUTH_PW']));
        if ($has_supplied_credentials){
            $user = TraccarUser::where('email' , '=' , $_SERVER['PHP_AUTH_USER'])->first();
            if (!empty($user)){
                $email = $user->email;
                $password = $user->password;
            }else{
                $email = null;
                $password = null;
            }
        }else{
            $email = null;
            $password = null;
        }
        $is_not_authenticated = (
            !$has_supplied_credentials ||
            $_SERVER['PHP_AUTH_USER'] != $email ||
            $_SERVER['PHP_AUTH_PW']   != $password
        );
        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            exit;
        }
        return $next($request);
    }
}
