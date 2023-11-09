<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use App\Services\UserService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends ApiBaseController
{
    public function __construct(protected UserService $user_service, protected EmailVerificationService $email_verification_service)
    {
    }

    public function register(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user_service->store($request->validated());
            $token = $user->createToken('auth_token')->plainTextToken;
            $email_verification = $this->email_verification_service->send($token);
            if (!$email_verification['success']) {
                return $this->send_error($email_verification['message'], [], $email_verification['status']);
            }
            $response = [
                'token' => $token,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'verification_code' => $email_verification['verification_code']
            ];
            DB::commit();
            return $this->send_response($response, "Your account has been registered. Please check your email to activate your account.");
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->send_error($exception->getMessage(), [],$exception->getCode());
        }
    }

    public function authenticate(AuthUserRequest $request)
    {
        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $user = Auth::user();
                if (!$user->hasVerifiedEmail()) {
                    return $this->send_error('Email not verified. Please verify your email.', [], 401);
                }
                $token = $user->createToken('auth_token')->plainTextToken;
                return $this->send_response([
                    'token' => $token,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email
                ], 'User login successfully.');
            } else {
                return $this->send_error('Unauthorised.', ['error' => 'Unauthorised'], 401);
            }
        } catch (\Exception $exception) {
            return $this->send_error($exception->getMessage(), [], $exception->getCode());
        }
    }
}
