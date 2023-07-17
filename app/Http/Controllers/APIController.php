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
            'location_id' => 'required',
        ]);
        // $add_user = User::create([
        //     'name' => 'Andreas Christian',
        //     'email' => $request->email,
        //     'password' => Hash::make($request->password)
        // ]);
        $user = User::where('email', $request->input('email'))->first();

        if (!$user || !Hash::check($request->input('password'), $user->password)) {
            return response()->json([
                'status' => false,
                'message' => 'The provided credentials are incorrect.'
            ]);
        }
        // Revoke previous tokens
        $user->tokens()->delete();

        $token = $user->createToken($request->input('location_id'))->plainTextToken;

        return response()->json([
            'status' => collect($token)->isNotEmpty() ? true : false,
            'message' => `Login berhasil`,
            'data_user' => $user,
            'location_id' => $request->input('location_id'),
            'access_token' => $token,
            'token_type' => 'Bearer'
        ]);
    }
}
