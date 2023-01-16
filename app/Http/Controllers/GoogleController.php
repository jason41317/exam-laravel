<?php
  
namespace App\Http\Controllers;
  
use App\Http\Controllers\Controller;
use App\Models\User;
use Socialite;
use Exception;
use Illuminate\Support\Facades\Auth;

class GoogleController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect()->getTargetUrl();
    }
      
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->stateless()->user();
            // var_dump($user);
            $finduser = User::where('google_id', $user->id)
            ->orWhere('email', $user->email)->first();
       
            if ( $finduser ) {
                $finduser->update([
                    'email' => $user->email,
                    'google_id'=> $user->id
                ]);
                $finduser->markEmailAsVerified();
                $passportToken = $finduser->createToken('Login Token');
                return response()->json($passportToken);
                
       
            } else {
                
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'google_id'=> $user->id,
                    'password' => 'dummypass'// you can change auto generate password here and send it via email but you need to add checking that the user need to change the password for security reasons
                ]);
      
                $newUser->markEmailAsVerified();
                $passportToken = $user->createToken('Login Token');
                return response()->json($passportToken);
            }
    
        } catch (Exception $e) {
            dd($e->getMessage());
        }
    }
}
