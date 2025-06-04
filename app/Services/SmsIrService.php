<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsIrService
{
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('SMSIR_API_KEY', 'qNGeorBdVIemtfwd9mbpyCth9xZWmiUy0TClt0Ebban6eg2Y5Gc2db6MkS3qfWxe');
    }

    /**
     * ارسال کد تایید با SMS.ir
     * @param string $mobile
     * @param string $code
     * @param string $type 'register' یا 'login'
     * @return array|bool
     */
    public function sendVerificationCode($mobile, $code, $type = 'register')
    {
        $templateId = $type === 'register' ? 756965 : 913518;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Accept' => 'text/plain',
            'x-api-key' => $this->apiKey,
        ])->post('https://api.sms.ir/v1/send/verify', [
            'mobile' => $mobile,
            'templateId' => $templateId,
            'parameters' => [
                [
                    'name' => 'Code',
                    'value' => $code,
                ]
            ]
        ]);

        if ($response->successful()) {
            return $response->json();
        } else {
            // می‌تونی اینجا لاگ هم بزنی
            return false;
        }
    }
} 