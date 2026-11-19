<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;

class IndexController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'code' => 1,
            'msg' => 'Laravel CMS API',
            'data' => [
                'version' => 'P4',
                'endpoints' => [
                    'POST /api/user/login',
                    'POST /api/user/register',
                    'GET /api/user/profile' => 'Header: X-Cms-Token',
                    'POST /api/user/changepassword' => 'Header: X-Cms-Token',
                    'POST /api/user/resetpassword' => 'username + mobile + password',
                ],
            ],
        ]);
    }
}
