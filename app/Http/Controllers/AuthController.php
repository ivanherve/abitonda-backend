<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Check the email and the password hashed to the database.
     * If these elements do not exist, the function shows an error message.
     * If they exist, the function will create a new token for the authenticated user if this one does not have a token yet.
     * If the user already has a token, the function will create another token to him/her or
     * it will update the database to insert a token id, the user id and a new token.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signIn(Request $request)
    {
        $pswd = $request->input('password');
        $email = $request->input('email');
        //return $this->errorRes([$email, $pswd],404);
        $user = DB::select("call log_in(?,?);", [$email, $pswd]);
        if (!$user) return $this->errorRes(["L'adresse ou le mot de passe rentré n'est pas correcte", $user, $email, $pswd], 401);
        //return $user[0]->User_Id;
        $userId = $user[0]->User_Id;
        $hasNotToken = Token::all()->where('User_Id', '=', $userId)->where('api_token', '=', null)->first();
        $tokenId = Token::all()->where('User_Id', '=', $userId)->where('api_token', '=', null)->pluck('Token_Id')->first();
        //return $this->errorRes([$tokenId, $hasNotToken], 404);
        if ($hasNotToken) {
            $token = Str::random(60);
            $newToken = DB::update("call update_token(?,?,?)", [$token, $tokenId, $userId]);
            $newToken = Token::all()->where('User_Id', '=', $userId)->where('api_token', '=', $token)->first();
            return $this->successRes(['user' => $user[0], 'token' => $newToken]);
        } else {
            $newToken = Token::create([
                'User_Id' => $userId,
                'api_token' => Str::random(60)
            ]);
        }
        return $this->successRes(['user' => $user[0], 'token' => $newToken]);
    }

    /**
     * Log out an authenticated user.
     * It get the token in use and update the database to set this token to null.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function signOut(Request $request)
    {
        $api_token = $request->header('Authorization');
        $token = Token::all()->where('api_token', '=', $api_token)->first();
        $tokenId = Token::all()->where('api_token', '=', $api_token)->pluck('Token_Id')->first();
        if (!$token) {
            return $this->errorRes('Vous n\'êtes pas connecté', 401);
        }
        $token = DB::select('UPDATE Token SET api_token = null WHERE token_id = ' . $tokenId . ';');
        $emptyTok = Token::all()->where('Token_id', '=', $tokenId)->pluck('api_token')->first();
        //        return $this->jsonRes('s',$emptyTok,200);
        if ($emptyTok == null) {
            return $this->successRes('Vous êtes maintenant déconnécté');
        }
        return $this->errorRes('Vous n\'êtes pas connecté', 401);
    }
}
