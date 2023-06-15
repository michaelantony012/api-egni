<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class APIController extends Controller
{
    //
    public function create_token(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'device_name' => 'required',
        ]);
        // $add_user = User::create([
        //     'name' => 'Andreas Christian',
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password)
        // ]);
        $user = User::where('email', $request->input('email'))->first();
  
        if (! $user || ! Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'error' => 'The provided credentials are incorrect.'
            ]);
        }
        // Revoke previous tokens
        $user->tokens()->delete();
        
        $token = $user->createToken($request->input('device_name'))->plainTextToken;
 
        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }   
}
