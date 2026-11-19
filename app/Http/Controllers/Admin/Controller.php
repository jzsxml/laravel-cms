<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller as BaseController;
use App\Services\Admin\AdminAuth;

abstract class Controller extends BaseController
{
    public function __construct(protected AdminAuth $adminAuth) {}
}
