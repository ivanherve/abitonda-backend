<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function checkPassword(Request $request)
    {
        $userId = $request->input('userId');
        if (!$userId) return $this->errorRes('De quel utilisateur s\'agit-il ?', 404);
        $oldpassword = $request->input('oldpassword');
        if (!$oldpassword) return $this->errorRes('Veuillez d\'abord insérer votre ancien mot de passe', 404);

        $user = User::all()->where('User_Id', '=', $userId);
        if (!$user) return $this->errorRes('Cet utilisateur n\'existe pas dans notre système', 404);

        $user = $user->first();

        $user = DB::select("call log_in(?,?)", [$user->EmailAddress, $oldpassword]);
        if (!$user) return $this->errorRes('Le mot de passe est incorect', 401);

        return $this->successRes('Le mot de passe est correct');
    }

    public function editPassword(Request $request)
    {
        # code...
        $userId = $request->input('userId');
        $newpassword = $request->input('newpassword');
        $oldpassword = $request->input('oldpassword');
        $confpassword = filter_var($request->input('confpassword'), FILTER_VALIDATE_BOOLEAN);

        if ($userId == Auth::user()->User_Id) {
            // L'utilisateur connecté veut changer son mdp
            $user = Auth::user();
            $user = DB::select("call log_in(?,?)", [$user->EmailAddress, $oldpassword]);
            if (!$user) return $this->errorRes('Le mot de passe actuel est incorect', 401);
            $user = $user[0];
        } else {
            // L'admin change le mdp
            $user = User::all()->where('User_Id', '=', $userId)->first();
            if (Auth::user()->Profil_Id < 3) return $this->errorRes('Unauthorized.', 401);
        }

        if (!$newpassword) return $this->errorRes('Veuillez insérer un nouveau mot de passe', 404);
        $newpassword = $this->checkPwdStrength($newpassword, $confpassword);
        if(!$newpassword->original["status"]) return $this->errorRes($newpassword->original["response"], 401);
        $newpassword = $newpassword->original["response"];
        if (!$confpassword) return $this->errorRes('Les mots de passe ne correspondent pas', 401);
        //return $this->debugRes(Auth::user());
        DB::update("call update_pwd(?,?)", [$newpassword, $user->User_Id]);
        return $this->successRes('Le mot de passe a bien été modifié');
    }
}
