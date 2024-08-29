<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthTokenController extends Controller
{
    public function redirectToProvider()
    {
        return redirect()->away(config('app.auth_server_url') . '/oauth/authorize?' . http_build_query([
            'client_id' => config('services.passport.client_id'),
            'redirect_uri' => config('services.passport.redirect'),
            'response_type' => 'code',
            'scope' => '',
        ]));
    }

    public function handleProviderCallback(Request $request)
    {
        $http = new Client();

        $response = $http->request('post', config('app.auth_server_url') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'authorization_code',
                'client_id' => config('services.passport.client_id'),
                'client_secret' => config('services.passport.client_secret'),
                'redirect_uri' => config('services.passport.redirect'),
                'code' => $request->code,
            ],
        ]);

        $token = json_decode((string) $response->getBody(), true)['access_token'];

        // Store the token in session or use it to authenticate API requests
        session(['token' => $token]);

        return redirect('/dashboard');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $http = new Client;

        $response = Http::post('http://127.0.0.1:8000/api/register', [
            'form_params' => [
                'client_id' => config('services.passport.client_id'),
                'secret' => config('services.passport.client_secret'),
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
            ],
        ]);


        $data = json_decode((string) $response->getBody(), true);
        // dump($data);
        session(['token' => $data['access_token']]);

        return redirect('/dashboard');
    }

    public function getUserInfo(Request $request)
    {
        $token = session('token');

        if (!$token) {
            return redirect('login');
        }

        $http = new Client;

        try {
            $response = $http->get(config('app.auth_server_url') . '/api/user', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $token,
                ],
            ]);

            $user = json_decode((string) $response->getBody(), true);

            // Use the user data as needed
            return view('user.profile', compact('user'));
        } catch (\Exception $e) {
            return redirect('login')->withErrors('Unable to retrieve user information.');
        }
    }
}
