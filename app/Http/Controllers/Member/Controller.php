<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller as BaseController;
use App\Services\Member\MemberAuth;

abstract class Controller extends BaseController
{
    public function __construct(protected MemberAuth $memberAuth) {}
}
