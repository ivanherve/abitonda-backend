<?php

namespace App\Http\Middleware;

use App\Token;
use App\User;
use Closure;

class AdminMiddleware
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
        $token = Token::all()->where('api_token','=',$request->header('Authorization'))->first();
        if(!$token) return response(['status'=> 0,'response' => ['Unauthorized. adto']], 401);
        $user = User::all()->where('User_Id','=',$token->User_Id)->first();
        if($user->Profil_Id < 3){
            return response(['status'=> 0,'response' => ['Unauthorized. ad']], 401);
        }
        return $next($request);
    }
}