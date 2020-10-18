<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * Enable to all controllers to return json responses to the client.
     * @param $status
     * @param $response
     * @param $httpCode
     * @return mixed
     */

    public $filesPath = './files/';

    public function jsonRes($status, $response, $httpCode)
    {
        return response()->json(['status' => $status, 'response' => $response], $httpCode);
    }

    public function successRes($response, $code = 200)
    {
        return response()->json(['status' => 1, 'response' => $response], $code);
    }

    public function errorRes($response, $code)
    {
        return response()->json(['status' => 0, 'response' => $response], $code);
    }    

    public function debugRes($response)
    {
        return response()->json(['status' => 0, 'response' => $response], 501);
    }

    public function download($path, $fileName)
    {
        return response()->download($path, $fileName);
    }

    public function transformFilename($title)
    {
        $title = explode(' ', $title);
        $newTitle = [];
        for ($i = 0; $i <= sizeof($title) - 1; $i++) {
            array_push($newTitle, strtoupper($title[$i][0]) . substr($title[$i], 1));
        }
        $title = implode('', $newTitle);
        return $title;
    }

    public function checkPwdStrength($password, $confPassword)
    {
        // Validate password strength
        $uppercase = preg_match('@[A-Z]@', $password);
        if (!$uppercase) return $this->errorRes('Le mot de passe doit au moins contenir une majuscule', 401);
        $lowercase = preg_match('@[a-z]@', $password);
        if (!$lowercase) return $this->errorRes('Le mot de passe doit au moins contenir une minuscule', 401);
        $number = preg_match('@[0-9]@', $password);
        if (!$number) return $this->errorRes('Le mot de passe doit au moins contenir un chiffre', 401);
        $specialChars = preg_match('@[^\w]@', $password);
        if (!$specialChars) return $this->errorRes('Le mot de passe doit au moins contenir un caractère spécial', 401);
        if (strlen($password) < 8) return $this->errorRes('Le mot de passe doit contenir plus de 8 caractères', 401);
        if (!filter_var($confPassword, FILTER_VALIDATE_BOOLEAN)) return $this->errorRes('Les mots de passe ne correspondent pas', 401);
        return $this->successRes($password);
    }

    public function public_path($path = null)
    {
        return rtrim(app()->basePath('public/' . $path), '/');
    }
}
