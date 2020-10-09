<?php

namespace App\Http\Middleware;

use App\Parents;
use App\Token;
use App\User;
use Closure;

class ParentMiddleware
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
        $token = Token::all()->where('api_token', '=', $request->header('Authorization'))->first();
        $parent = Parents::all()->where('User_Id', '=', $token->User_Id)->first();
        $user = User::all()->where('User_Id', '=', $token->User_Id)->first();
        if ($user->Profil_Id >= 3 || sizeof([$parent]) >= 1) {
            return $next($request);
        }        
        return response(['status' => 0, 'response' => ['Unauthorized. pa', $parent, $user->Profil_Id]], 401);
    }
}
