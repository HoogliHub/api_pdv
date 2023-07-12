<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $url = config('enjoy.url');
            $reply = Http::get($url . 'api/clients');

            $response = $reply->json();

            return $response;
        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $url = config('enjoy.url');

            $requestClient = $request->all();


            if (isset($requestClient['Customer']['name'])) {
                    $data['name'] = $requestClient['Customer']['name'];
            }

            if (isset($requestClient['Customer']['cpf'])) {
                    $data['cpf'] = $requestClient['Customer']['cpf'];
            }

            if (isset($requestClient['Customer']['cellphone'])) {
                    $data['phone'] = $requestClient['Customer']['cellphone'];
            }

            if (isset($requestClient['Customer']['city'])) {
                    $data['city'] = $requestClient['Customer']['city'];
            }

            if (isset($requestClient['Customer']['email'])) {
                    $data['email'] = $requestClient['Customer']['email'];
            }

            if (isset($requestClient['Customer']['state'])) {
                $data['state'] = $requestClient['Customer']['state'];
            }
            if (isset($requestClient['Customer']['address'])) {
                $data['address'] = $requestClient['Customer']['address'];
            }

            if (isset($requestClient['Customer']['zip_code'])) {
                $data['postal_code'] = $requestClient['Customer']['zip_code'];
            }

            $response = Http::post($url . 'api/client', $data);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $url = config('enjoy.url');

            $reply = Http::get($url . 'api/client/' . $id);

            $response = $reply->json();

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, string $id)
    {
        try {
            $url = config('enjoy.url');

            $requestClient = $request->all();


            if (isset($requestClient['Customer']['name'])) {
                $data['name'] = $requestClient['Customer']['name'];
            }

            if (isset($requestClient['Customer']['cpf'])) {
                $data['cpf'] = $requestClient['Customer']['cpf'];
            }

            if (isset($requestClient['Customer']['cellphone'])) {
                $data['phone'] = $requestClient['Customer']['cellphone'];
            }

            if (isset($requestClient['Customer']['city'])) {
                $data['city'] = $requestClient['Customer']['city'];
            }

            if (isset($requestClient['Customer']['email'])) {
                $data['email'] = $requestClient['Customer']['email'];
            }

            if (isset($requestClient['Customer']['state'])) {
                $data['state'] = $requestClient['Customer']['state'];
            }
            if (isset($requestClient['Customer']['address'])) {
                $data['address'] = $requestClient['Customer']['address'];
            }

            if (isset($requestClient['Customer']['zip_code'])) {
                $data['postal_code'] = $requestClient['Customer']['zip_code'];
            }

            $response = Http::put($url . 'api/client/'.$id, $data);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
       //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $url = config('enjoy.url');

            $response = Http::delete($url . 'api/client/' . $id);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }
}
