<?php

namespace App\Services\Sms;

use Illuminate\Support\Facades\Log;

class SmsService
{
    /**
     * @return array{code: int, msg: string}
     */
    public function send(string $mobile, string $content, array $context = []): array
    {
        $driver = (string) config('cms.sms.driver', 'log');

        if ($driver === 'log') {
            Log::channel('single')->info('CMS SMS', [
                'mobile' => $mobile,
                'content' => $content,
                'context' => $context,
            ]);

            return ['code' => 1, 'msg' => '短信已记录到日志（模拟发送）'];
        }

        if ($driver === 'aliyun') {
            $key = config('cms.sms.aliyun.access_key');
            if ($key === '' || $key === null) {
                return ['code' => 0, 'msg' => '未配置阿里云短信密钥'];
            }

            return ['code' => 0, 'msg' => '阿里云短信需安装 SDK 并完善 SmsService::sendAliyun'];
        }

        return ['code' => 0, 'msg' => '未知短信驱动: '.$driver];
    }
}
