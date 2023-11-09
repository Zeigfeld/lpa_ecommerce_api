<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function store(array $data) : User{
        $data['password'] = Hash::make($data['password']);
        $user = new User($data);
        $user->save();
        return $user;
    }

    public function update(int $id, array $data) : User{
        $user = User::find($id);
        $user->fill($data);
        $user->update();
        return $user;
    }
}
