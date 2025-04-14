<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validate registration data
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Create the user
        $user = User::create([
            'name'     => $request->name,
            'username' => $request->username,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'rank'     => 'Novice',
        ]);

        // Generate 2FA code
        $code = rand(100000, 999999);
        $user->two_fa_code = $code;
        $user->two_fa_expires_at = Carbon::now()->addMinutes(10);
        $user->save();

        // Send 2FA code to the user's email
        Mail::raw("Your 2FA code is: $code", function ($message) use ($user) {
            $message->to($user->email)->subject('Your 2FA Code');
        });

        return response()->json([
            'message' => 'Registration successful. A 2FA code has been sent to your email. Please verify it.',
        ]);
    }

    public function verify2FA(Request $request)
    {
        // Validate the incoming request
        $request->validate([
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        // Look for the user and verify the 2FA code
        $user = User::where('email', $request->email)
                    ->where('two_fa_code', $request->code)
                    ->where('two_fa_expires_at', '>', Carbon::now())
                    ->first();

        // If no user or invalid/expired code
        if (!$user) {
            return response()->json(['error' => 'Invalid or expired 2FA code'], 401);
        }

        // Clear the 2FA code after successful verification
        $user->two_fa_code = null;
        $user->two_fa_expires_at = null;
        $user->save();

        // Generate and return a JWT token
        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => '2FA verified successfully.',
            'token' => $token,
            'user' => $user,
        ]);
    }
}