<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\User;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;

class AuthController extends Controller
{
    public function __construct()
    {
         $this->middleware('auth:sanctum')->only('logout');
    }
    
    public function register(RegisterRequest $request)
    {
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            //'remember_token' => Str::random(10),
        ]);

        $token = $user->createToken('mytoken');

        $response = [
            'data' => new UserResource($user),
            'token' => $token->plainTextToken
        ];

        return response($response,201);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email',$request['email'])->first();

        if (!$user || !Hash::check($request['password'], $user->password)) {
            return response([
                'massage' => 'There is no such user'
            ],401);
        }

        $token = $user->createToken('mytoken')->plainTextToken;
        
        $response = [
            'data' => new UserResource($user),
            'token' => $token
        ];

        return response($response,200);
    }

    public function logout(Request $request)
    {
        auth()->user()->tokens()->delete();

        return response([
            'massage' => 'Logged Out!'
        ], 200);
    }
}
