<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('auth/login.php');
    }
     public function privacy()
    {
        return view('pages/privacy_policy');
    }
}