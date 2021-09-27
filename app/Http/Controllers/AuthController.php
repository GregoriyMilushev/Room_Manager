<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;

class AuthController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:sanctum')->only('logout');
    }
    
    public function register(Request $request)
    {
        $fields = $request->validate([
            'name' => 'required|string|max:200',
            'email' => 'required|string|max:200|unique:users,email',
            'password' => 'required|string|confirmed|min:8',
        ]);

        $user = User::create([
            'name' => $fields['name'],
            'email' => $fields['email'],
            'password' => bcrypt($fields['password']),
            //'remember_token' => Str::random(10),
        ]);

        $token = $user->createToken('mytoken');

        $response = [
            'user' => $user,
            'token' => $token->plainTextToken
        ];

        return response($response,201);
    }

    public function login(Request $request)
    {
        $fields = $request->validate([
            'email' => 'required|string|max:200',
            'password' => 'required|string|min:8',
        ]);

        $user = User::where('email',$fields['email'])->first();

        if (!$user || !Hash::check($fields['password'], $user->password)) {
            return response([
                'massage' => 'There is no such user'
            ],401);
        }

        $token = $user->createToken('mytoken')->plainTextToken;
        
        $response = [
            'user' => $user,
            'token' => $token
        ];

        return response($response,201);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return [
            'massage' => 'Logged Out!'
        ];
    }
}
