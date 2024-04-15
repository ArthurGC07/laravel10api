<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
   public function register(Request $request) {
    //creates a validator, so the input is validate
    $validator = Validator::make($request->all(), [
        'username'=>['required', 'min:3', Rule::unique('users', 'username')],
        'email'=>['email', 'required', Rule::unique('users', 'email')],
        'password'=>['required', 'min:8', 'confirmed'] //the confirmed tag need to send another param with _confirmation
    ]);
    //if the validator fails it returns a json response
    if($validator->fails()){
        return response()->json([
            'message' => 'Validation Errors',
            'error' => $validator->errors()
        ], 422);
    }
    //sets the incoming fields hash'em and creates a new user
    $incomingFields = $validator->validate();
    $incomingFields['password'] = Hash::make($incomingFields['password']);

    $user = User::create($incomingFields);
    return response()->json([
        'message' => 'User successfully registered',
        'user' => [
            'username'=>$user->username,
            'email'=>$user->email,
            'password'=>$user->password
        ]
    ], 201); //201 created
   }

   public function login(Request $request) {
    $incomingFields = $request->validate([
        'username' => 'required',
        'password' => 'required',
    ]);

    if(auth()->attempt($incomingFields)){
        $user = User::where('username', $incomingFields['username'])->first();
        $token = $user->createToken('logintoken')->plainTextToken;
        return $token;
    }
    return 'sorry';

   }
}
