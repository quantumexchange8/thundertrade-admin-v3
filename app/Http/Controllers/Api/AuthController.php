<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ForgotPassword;
use App\Models\EmailOtp;
use App\Models\User;
use App\Rules\OtpVerify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|max:255',
            'password' => 'required',
        ]);

        if (!User::where('email', $request->email)->whereNot('merchant_id', NULL)->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is incorrect'
            ]);
        }

        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password], $request->remember)) {
            return response()->json([
                'success' => false,
                'message' => 'Email or password is incorrect'
            ]);
        }

        $user = User::where('email', $request->email)->first();
        $authToken = $user->createToken('auth-token')->plainTextToken;

        activity('activity-log')->causedBy(Auth::user())->log('login');
        return response()->json([
            'success' => true,
            'message' => 'Logged In',
            'data' => $authToken
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        activity('activity-log')->causedBy(Auth::user())->log('logout');
        return response()->json([
            'success' => true,
            'message' => 'Logged Out'
        ]);
    }

    public function forgotPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid Email'
            ]);
        }

        $request->validate([
            'tac' => ['required', new OtpVerify($email, 'forgot_password')],
        ]);

        EmailOtp::where('email', $email)->delete();
        $chars = "0123456789abcdefghijklmnopqrstuvwxyz!@#$%^&*()ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $passwordLength = 8;
        $password = "";

        for ($i = 0; $i <= $passwordLength; $i++) {
            $randomNumber = rand(0, strlen($chars));
            $password .= substr($chars, $randomNumber, 1);
        }

        $user->password = Hash::make($password);
        $user->remember_token = null;
        $user->save();
        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->tokens()->delete();
        Mail::to($email)->send(new ForgotPassword($password));

        activity('activity-log')->causedBy($user)->log('reset-password');
        return response()->json(['success' => true, 'message' => 'New Password Has Been Sent to Your Email']);
    }
}
