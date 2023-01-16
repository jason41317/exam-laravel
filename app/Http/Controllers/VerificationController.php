<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

class VerificationController extends Controller
{
    public function verify(Request $request) {
        $user = User::findOrFail($request->id);

        if (! hash_equals((string) $request->hash, sha1($user->getEmailForVerification()))) {
            return response()->json([
                "message" => "Unauthorized",
                "success" => false
            ]);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                "message" => "Email already verified!",
                "success" => true
            ]);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
            return response()->json([
                "message" => "Email successfully verified!",
                "success" => true
            ]);
        }

        // $authController = new AuthController;
        // return $authController->getAuthUser();
    }

    public function resendVerificationEmail (Request $request) {
        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return response()->json([
                "message" => "Failed to send!",
                "success" => false
            ]);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            "message" => "Check your email!",
            "success" => true
        ]);
    }
}