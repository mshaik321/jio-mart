<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_of_birth' => $user->customer?->date_of_birth?->toDateString(),
            'gender' => $user->customer?->gender?->value,
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['sometimes', 'string', 'max:255'],
            'phone' => ['sometimes', 'nullable', 'string', 'max:20'],
            'date_of_birth' => ['sometimes', 'nullable', 'date'],
            'gender' => ['sometimes', 'nullable', 'in:male,female,other'],
        ]);

        $user = $request->user();
        $user->update(array_intersect_key($data, array_flip(['name', 'phone'])));

        if ($user->customer) {
            $user->customer->update(array_intersect_key($data, array_flip(['date_of_birth', 'gender'])));
        }

        return response()->json(['message' => 'Profile updated.']);
    }
}
