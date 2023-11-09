<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Requests\AuthUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthController extends ApiBaseController
{
    public function __construct(protected UserService $user_service){}

    public function register(StoreUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $user = $this->user_service->store($request->validated());
            $token = $user->createToken('auth_token')->plainTextToken;
            $response = [
                'token' => $token,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email
            ];
            DB::commit();
            return $this->send_response($response, "Your account has been registered. Please check your email to activate your account.");
        }catch (\Exception $throwable){
            DB::rollBack();
            return $this->send_error($throwable->getMessage(), $throwable->getCode());
        }
    }

    public function authenticate(AuthUserRequest $request)
    {
        try {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
                $user = Auth::user();
                $token  =  $user->createToken('auth_token')->plainTextToken;
                return $this->send_response([
                    'token' => $token,
                    'first_name' => $user->first_name,
                    'last_name' => $user->last_name,
                    'email' => $user->email
                ], 'User login successfully.');
            }
            else{
                return $this->send_error('Unauthorised.', ['error'=>'Unauthorised'], 401);
            }
        }catch (\Exception $exception){
            return $this->send_error($exception->getMessage(), $exception->getCode());
        }
    }
}
