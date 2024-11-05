<?php

namespace App\Http\Controllers;

use App\Events\UserRegister;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function PHPUnit\Framework\isNull;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $fields = $request->all();
        
        $errors = Validator::make($fields, [
            'email' => 'required|email|unique:users,email',
            'password'  => 'required|min:6|max:8'
        ]);
        
        if ($errors->fails()) {
            return response($errors->errors()->all(), 422);
        }        

        $user = User::create([
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            'isValidEmail' => User::IS_INVALID_EMAIL,
            'remember_token'    => $this->generateRandomCode()
        ]);

        UserRegister::dispatch($user);
        
        return response([
            'user'  => $user,
            'message' => 'User Created Successfully',
        ], 200);
    }

    public function validEmail($token)
    {
        User::where('remember_token', $token)
            ->update(['isValidEmail' => User::IS_VALID_EMAIL]);

        return redirect('/app/login');
    }

    function generateRandomCode()
    {
        $code = Str::random(10) . time();
        
        return $code;
    }

}
