<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function auth()
    {  
        return response()->json(['message' => 'Token válido', 'code' => 200], 200);
    }
}
