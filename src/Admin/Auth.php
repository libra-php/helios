<?php

namespace Helios\Admin;

class Auth
{
    public static function signIn()
    {

    }

    public static function signOut()
    {
        session()->destroy();
        redirect(findRoute("sign-in.index"));
    }

    public static function register()
    {

    }
}
