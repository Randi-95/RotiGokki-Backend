<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed', 
            'role' => ['required', Rule::in(['superadmin', 'admin_outlet'])], 
            'outlet_id' => 'required_if:role,admin_outlet|nullable|integer' 
        ]);
        $data = $validated;
        $data['password'] = Hash::make($validated['password']); 
        $data['outlet_id'] = $validated['role'] === 'admin_outlet' ? $validated['outlet_id'] : null;

        $user = User::create($data);

        $token = $user->createToken('admingokki')->plainTextToken;
        

        return response()->json([
            'message' => 'Registrasi berhasil',
            'authToken' => $token,
            'user' => $user
        ], 201); 
    }

    function login(Request $request) {
         $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);
 
    $user = User::where('email', $request->email)->first();
 
    if (! $user || !Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['Email Atau password salah'],
        ]);
    }
 
    $token = $user->createToken('admingokki')->plainTextToken;

    return response()->json([
            'message' => 'Login successful',
            'authToken' => $token,
            'user' => $user 
    ]);
    } 

    public function logout(Request $request)
    {
    $request->user()->currentAccessToken()->delete();
        
    return response()->json([
        'message' => 'Berhasil Logout'
    ]);
    }
}