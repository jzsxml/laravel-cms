<?php

namespace App\Http\Controllers\Admin\General;

use App\Http\Controllers\Admin\Controller;
use App\Services\Sms\SmsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SmsController extends Controller
{
    public function index(): View
    {
        return view('admin.general.sms.index', [
            'title' => '短信测试',
            'driver' => config('cms.sms.driver', 'log'),
        ]);
    }

    public function send(Request $request, SmsService $sms): JsonResponse
    {
        $data = $request->validate([
            'mobile' => ['required', 'string', 'max:20'],
            'content' => ['required', 'string', 'max:500'],
        ]);

        $result = $sms->send($data['mobile'], $data['content'], ['admin_id' => $this->adminAuth->adminId()]);
        $this->adminAuth->recordLog($request, '短信', '测试发送: '.$data['mobile']);

        return response()->json($result);
    }
}
