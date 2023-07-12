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

            }

            if (isset($requestClient['Customer']['rg'])) {

            }

            if (isset($requestClient['Customer']['cpf'])) {

            }

            if (isset($requestClient['Customer']['phone'])) {

            }

            if (isset($requestClient['Customer']['cellphone'])) {

            }

            if (isset($requestClient['Customer']['email'])) {

            }

            if (isset($requestClient['Customer']['address'])) {

            }
            if (isset($requestClient['Customer']['zip_code'])) {

            }

            if (isset($requestClient['Customer']['number'])) {

            }

            if (isset($requestClient['Customer']['complement'])) {

            }

            if (isset($requestClient['Customer']['city'])) {

            }

            if (isset($requestClient['Customer']['state'])) {

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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $url = config('enjoy.url');

            $requestClient = $request->all();


            if (isset($requestClient['Customer']['name'])) {

            }

            if (isset($requestClient['Customer']['rg'])) {

            }

            if (isset($requestClient['Customer']['cpf'])) {

            }

            if (isset($requestClient['Customer']['phone'])) {

            }

            if (isset($requestClient['Customer']['cellphone'])) {

            }

            if (isset($requestClient['Customer']['email'])) {

            }

            if (isset($requestClient['Customer']['address'])) {

            }
            if (isset($requestClient['Customer']['zip_code'])) {

            }

            if (isset($requestClient['Customer']['number'])) {

            }

            if (isset($requestClient['Customer']['complement'])) {

            }

            if (isset($requestClient['Customer']['city'])) {

            }

            if (isset($requestClient['Customer']['state'])) {

            }

            $response = Http::post($url . 'api/client/'.$id, $data);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
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
