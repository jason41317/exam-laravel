<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();
            // var_dump(Config::get('client.id'));
            $http = new \GuzzleHttp\Client;
            $response = $http->post(url('/') . '/oauth/token', [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => Config::get('client.id'),
                    'client_secret' => Config::get('client.secret'),
                    'username' => $data['email'],
                    'password' => $data['password'],
                    'scope' => '',
                ],
            ]);

            
        }
        catch(RequestException $e) {
            $response = $e->getResponse(); //Get error response body
        }
        
        
        return json_encode(json_decode((string) $response->getBody(), true));
    }

    public function getAuthUser()
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function register(RegisterRequest $registerRequest)
    {
        $user = User::create([
            'email' => $registerRequest->email,
            'first_name' => $registerRequest->first_name,
            'middle_name' => $registerRequest->middle_name,
            'last_name' => $registerRequest->last_name,
            'password' => Hash::make($registerRequest->password)
        ]);

        event(new Registered($user));

        return (new UserResource($user))
            ->response()
            ->setStatusCode(201);
    }

    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return response()->json('Logged out successfully', 200);
    }
}