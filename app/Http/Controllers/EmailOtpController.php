<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Models\EmailOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class EmailOtpController extends Controller
{
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255'],
            'action' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->all()
            ], 422);
        }

        $user = User::where('email', $validator->validated()['email'])->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Email Address Not Found'
            ], 422);
        }

        $code = rand(100000, 999999);
        $rec = EmailOtp::firstOrCreate(['email' => $request->email, 'action' => $request->action], [
            'code' => $code,
            'created_at' => now()
        ]);

        $limit = 1;
        if (!$rec->wasRecentlyCreated) {
            $diff = now()->diffInMinutes($rec->created_at);
            if ($diff >= $limit) {
                EmailOtp::where('email', $request->email)->delete();
                EmailOtp::firstOrCreate(['email' => $request->email, 'action' => $request->action], [
                    'code' => $code,
                    'created_at' => now()
                ]);
                Mail::to($request->email)->send(new VerificationEmail($code));
                return response()->json(['success' => true, 'message' => 'Resent Email']);
            } else {

                return response()->json(['success' => false, 'message' => 'Cannot resent email within ' . $limit . ' minutes'], 422);
            }
        } else {
            EmailOtp::where('email', $request->email)->whereNot('action', $request->action)->delete();
        }



        Mail::to($request->email)->send(new VerificationEmail($code));

        return response()->json(['success' => true, 'message' => 'Email Sent']);
    }

    public function send(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'action' => ['required'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ]);
        }

        $user = Auth::user();
        $email = $user->email;

        $code = rand(100000, 999999);
        $rec = EmailOtp::firstOrCreate(['email' => $email, 'action' => $request->action], [
            'code' => $code,
            'created_at' => now()
        ]);

        $limit = 1;
        if (!$rec->wasRecentlyCreated) {
            $diff = now()->diffInMinutes($rec->created_at);
            if ($diff >= $limit) {
                EmailOtp::where('email', $email)->delete();
                EmailOtp::firstOrCreate(['email' => $email, 'action' => $request->action], [
                    'code' => $code,
                    'created_at' => now()
                ]);
                Mail::to($email)->send(new VerificationEmail($user->name, $code));
                return response()->json(['success' => true, 'message' => 'Resent Email']);
            } else {

                return response()->json(['success' => false, 'message' => 'Cannot resent email within ' . $limit . ' minutes'], 422);
            }
        } else {
            EmailOtp::where('email', $request->email)->whereNot('action', $request->action)->delete();
        }



        Mail::to($email)->send(new VerificationEmail($user->name, $code));

        return response()->json(['success' => true, 'message' => 'Email Sent']);
    }
}
