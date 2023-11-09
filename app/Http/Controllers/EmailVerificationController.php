<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\EmailVerfiyRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmailVerificationController extends ApiBaseController
{
    public function __construct(protected EmailVerificationService $email_verification_service){}
    public function send_verification(string $token)
    {
        try {
            $email_verification = $this->email_verification_service->send($token);
            if (!$email_verification['success']) {
                return $this->send_error($email_verification['message'], [],$email_verification['status']);
            }
            $response = [
                'token' => $token,
                'verification_code' => $email_verification['verification_code']
            ];
            return $this->send_response($response, "Email Verification has been sent. Please check your email.");
        }catch (\Exception $exception){
            return $this->send_error($exception->getMessage(), [],$exception->getCode());
        }
    }

    public function verify(EmailVerfiyRequest $request){
        try {
            $data = $request->validated();
            $token = $data['token'];
            $verification_code= $data['verification_code'];
            $verification = $this->email_verification_service->verify($token, $verification_code);
            if(!$verification['success']){
                return $this->send_error($verification['message'], [], 500);
            }
            return $this->send_response([], "Email has been verified.");
        }catch (\Exception $exception){
            return $this->send_error($exception->getMessage(), [], $exception->getCode());
        }
    }
}
