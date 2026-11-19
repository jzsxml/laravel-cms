<?php

namespace App\Http\Controllers\Member;

use Illuminate\View\View;

class IndexController extends Controller
{
    public function index(): View
    {
        $member = $this->memberAuth->member();

        return view('member.index', [
            'member' => $member,
        ]);
    }
}
