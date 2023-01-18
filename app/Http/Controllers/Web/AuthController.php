<?php

namespace App\Http\Controllers\Web;

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

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|max:255',
            'password' => 'required',
        ]);


        if (!Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            return response()->json(['success' => false, 'message' => 'Invalid user or password']);
        }
        activity()->causedBy(Auth::user())->log('Login');

        return response()->json(['success' => true, "message" => 'Success']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    public function forgotPassword(Request $request)
    {

        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Invalid Email']);
        }

        $request->validate([
            'otp' => ['required', new OtpVerify($email, 'forgot_password')],
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

        activity()->causedBy($user)->log('Reset Password');
        return response()->json(['success' => true, 'message' => 'New Password Has Been Sent to Your Email']);
    }
}
