<?php

namespace App\Http\Middleware;

use App\Teacher;
use App\Token;
use App\User;
use Closure;

class TeacherMiddleware
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
        if(!$token) return response(['status' => 0, 'response' => ['Unauthorized. ad', $token]], 401);
        $teacher = Teacher::all()->where('User_Id', '=', $token->User_Id);
        $user = User::all()->where('User_Id', '=', $token->User_Id)->first();
        if ($user->Profil_Id >= 3) {
            return $next($request);
        }
        if(sizeof($teacher) >= 1) return $next($request);
        return response(['status' => 0, 'response' => ['Unauthorized. te', $teacher, $user->Profil_Id]], 401);
    }
}
