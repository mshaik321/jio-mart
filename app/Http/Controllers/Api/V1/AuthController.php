<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20', 'unique:users,phone'],
            'password' => ['required', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => Hash::make($data['password']),
        ]);

        $user->assignRole('Customer');
        Customer::create(['user_id' => $user->id]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]], 201);
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('web')->validate($data)) {
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $user = User::where('email', $data['email'])->first();
        $token = $user->createToken('api')->plainTextToken;

        return response()->json(['token' => $token, 'user' => ['id' => $user->id, 'name' => $user->name, 'email' => $user->email]]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out.']);
    }
}
