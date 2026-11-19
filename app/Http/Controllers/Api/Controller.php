<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller as BaseController;
use App\Models\Api\Member;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    protected function member(Request $request): ?Member
    {
        $member = $request->attributes->get('member');

        return $member instanceof Member ? $member : null;
    }
}
