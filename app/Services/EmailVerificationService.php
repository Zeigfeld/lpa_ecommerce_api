<?php

namespace App\Services;

use App\Mail\VerifyEmailEmail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Laravel\Sanctum\PersonalAccessToken;

class EmailVerificationService
{
    public function send(string $token): array
    {
        try {
            $personal_access_token = PersonalAccessToken::findToken($token);
            $validation_result = $this->validate_token($personal_access_token);
            if (!$validation_result['success']) {
                return $validation_result;
            }
            $user = $validation_result['user'];
            $verification_code = rand(1000, 9999);
            $user->verification_code = $verification_code;
            $user->save();
            Mail::to($user->email)->send(new VerifyEmailEmail($verification_code));
            return [
                "success" => true,
                "message" => "Verification email has been sent",
                "status" => 200,
                'verification_code' => $verification_code,
                "user_id" => $user->id
            ];
        } catch (\Exception $exception) {
            return [
                "success" => false,
                "message" => $exception->getMessage(),
                "status" => $exception->getCode()
            ];
        }
    }

    public function verify(string $token, string $verification_code) : array
    {
        DB::beginTransaction();
        try{
            $personal_access_token = PersonalAccessToken::findToken($token);
            $validation_result = $this->validate_token($personal_access_token);
            if (!$validation_result['success']) {
                return $validation_result;
            }
            $user = $validation_result['user'];
            if($user->verification_code != $verification_code){
                return [
                    'success' => false,
                    'message' => 'Verification Code does not match our record.'
                ];
            }
            $user->email_verified_at = now();
            $user->save();
            DB::commit();
            return [
                'success' => true,
                'message' => 'Account has been verified',
            ];
        }catch (\Exception $exception){
            DB::rollBack();
            return [
                'success' => false,
                'message' => $exception->getMessage()
            ];
        }
    }

    public function validate_token($personal_access_token): array
    {
        if ($personal_access_token) {
            $user = $personal_access_token->tokenable;
            return [
                "success" => true,
                "user" => $user
            ];
        } else {
            return [
                "success" => false,
                "message" => "Could not find email token",
                "status" => 404
            ];
        }
    }
}
