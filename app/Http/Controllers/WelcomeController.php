<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class WelcomeController extends Controller
{
    public function index(): View
    {
        return view('welcome', [
            'adminPath' => config('cms.admin_path', 'admin'),
            'memberPath' => config('cms.member_path', 'user'),
        ]);
    }
}
