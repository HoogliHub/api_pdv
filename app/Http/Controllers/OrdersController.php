<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class OrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {

            $url = config('enjoy.url');
            $reply = Http::get($url . 'api/orders');

            $response = $reply->json();

            return $response;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
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

            $requestOrder = $request->all();


            if (isset($requestOrder['Order']['Customer'])) {
                $data['customer'] = $requestOrder['Order']['Customer'];
            }

            if(isset($requestOrder['Order']['payment_form'])){
                $data['payment_type'] = $requestOrder['Order']['payment_form'];
            }

            if(isset($requestOrder['Order']['shipment_value'])){
                $data['shipment_value'] = $requestOrder['Order']['shipment_value'];
            }

            $response = Http::post($url . 'api/order', $data);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $url = config('enjoy.url');

            $reply = Http::get($url . 'api/order/' . $id);

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
        if (isset($requestClient['Order']['point_sale'])) {

        }

        if (isset($requestClient['Order']['shipment'])) {

        }

        if (isset($requestClient['Order']['shipment_value'])) {

        }

        if (isset($requestClient['Order']['payment_form'])) {
            $data['city'] = $requestClient['Customer']['city'];
        }

        if (isset($requestClient['Order']['Customer'])) {

        }

        if (isset($requestClient['Order']['CustomerAddress'])) {

        }

        if (isset($requestClient['Order']['ProductsSold'])) {

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $url = config('enjoy.url');

            $response = Http::delete($url . 'api/order/' . $id);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }
}
