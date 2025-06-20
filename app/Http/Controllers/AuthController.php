<?php

namespace App\Http\Controllers;

use App\Models\User;
use Auth;
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
    public function login(Request $request)
    {
        $request->validate(['email' => 'required|email', 'password' => 'required|string',]);
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // Generate and send 2FA code
        $code = rand(100000, 999999);
        $user->two_fa_code = $code;
        $user->two_fa_expires_at = now()->addMinutes(10);
        $user->save();

        Mail::raw("Your 2FA code is: $code", function ($message) use ($user) {
            $message->to($user->email)->subject('Your 2FA Code');
        });

        return response()->json([
            'message' => '2FA code sent to your email. Please verify it.',
        ]);
    }
    public function logout(Request $request)
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());


            return response()->json([
                'message' => 'Successfully logged out',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to logout'], 500);
        }
    }

    public function user(Request $request)
    {
        try {
            // Get the authenticated user
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'error' => 'User not authenticated',
                    'message' => 'Authentication required'
                ], 401);
            }
            return response()->json([
                'success' => true,
                'user' => $user,
            ]);

        } catch (\Illuminate\Auth\AuthenticationException $e) {
            return response()->json([
                'error' => 'Authentication failed',
                'message' => 'Invalid or expired token'
            ], 401);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Server error',
                'message' => 'Failed to fetch user profile'
            ], 500);
        }
    }
}
