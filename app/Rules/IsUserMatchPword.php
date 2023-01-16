<?php

namespace App\Rules;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class IsUserMatchPword implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct($email)
    {
        $this->_email = $email;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $user = User::where(function ($q) {
            $q->where('email', $this->_email);
        })
            ->first();

        if ($user && Hash::check($value, $user->password)) {
            $user->update(['last_login_at' => Carbon::now()]);
        }

        return $user && Hash::check($value, $user->password);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Sorry! We couldn\'t find this user. The :attribute maybe incorrect.';
    }
}