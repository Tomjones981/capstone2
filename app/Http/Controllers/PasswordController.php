<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; 
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
class PasswordController extends Controller
{
    public function changePassword(Request $request)
    { 
        if (!Auth::check()) {
            return response()->json([
                'message' => 'Unauthorized. Please log in to change the password.'
            ], 401);
        }
 
        $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password' => 'required|string|min:8|confirmed',
        ]);
 
        $user = Auth::user();
 
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 400);
        }
 
        $user->password = Hash::make($request->new_password);
        $user->save();
        Log::info('Password changed for user: ' . $user->id);

        return response()->json([
            'message' => 'Password changed successfully.'
        ], 200);
    }
}
