<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function auth(Request $request)
    {
        $url = env('APP_URL');
        $token = $request->all();

        try {
            $response = Http::post($url.'api/auth', [
                'consumer_key' => $token['consumer_key'],
                'consumer_secret' => $token['consumer_secret'],
                'code' => $token['code']
            ]);

            if ($response->status() == 200) {
                echo $response->body();
            } else {
                echo 'Unexpected HTTP status: ' . $response->status() . ' ' . $response->reason();
            }
        } catch (\Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }
    
}
