<?php

namespace App\Rules;

use App\Models\EmailOtp;
use Illuminate\Contracts\Validation\InvokableRule;

class OtpVerify implements InvokableRule
{
    public $email, $action;
    public function __construct($email, $action)
    {
        $this->email = $email;
        $this->action = $action;
    }
    /**
     * Run the validation rule.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     * @return void
     */
    public function __invoke($attribute, $value, $fail)
    {
        $rec = EmailOtp::firstWhere(['email' => $this->email, 'action' => $this->action]);
        $limit = 5;
        if ($rec) {
            $diff = now()->diffInMinutes($rec->created_at);
            if ($diff >= $limit) {
                EmailOtp::where('email', $this->email)->delete();
                return $fail('OTP Expired');
            } else {
                if ($rec->code !== $value) {
                    return $fail('Invalid OTP');
                }
            }
        } else {
            return $fail('Invalid OTP');
        }
    }
}
