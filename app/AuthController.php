<?php
/**
 * Created by PhpStorm.
 * User: AKT
 * Date: 12/10/2015
 * Time: 10:11
 */
namespace App;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController
{
    public function verify($username, $password)
    {
        $credentials = [
            'email'    => $username,
            'password' => $password,
        ];

        if (Auth::once($credentials)) {
            return Auth::user()->id;
        }

        return false;
    }
}

