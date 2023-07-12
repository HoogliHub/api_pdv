<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ProductsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {

            $url = config('enjoy.url');
            $reply = Http::get($url . 'api/products');

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

            $requestProduct = $request->all();

            $data = [
                "added_by" => "pdv",
            ];

            if (isset($requestProduct['Product']['name'])) {
                $data["name"] = $requestProduct['Product']['name'];
                $data["slug"] = $requestProduct['Product']['name'];
            }

            if (isset($requestProduct['Product']['category_id'])) {
                $data["category_id"] = $requestProduct['Product']['category_id'];
            }

            if (isset($requestProduct['Product']['brand'])) {
                $data["brand_name"] = $requestProduct['Product']['brand'];
            }

            if (isset($requestProduct['Product']['weight'])) {
                $data["weight"] = $requestProduct['Product']['weight'];
            }

            if (isset($requestProduct['Product']['price'])) {
                $data["unit_price"] = $requestProduct['Product']['price'];
            }

            if (isset($requestProduct['Product']['description'])) {
                $data["description"] = $requestProduct['Product']['description'];
            }

            if (isset($requestProduct['Product']['stock'])) {
                $data["current_stock"] = $requestProduct['Product']['stock'];
            }


            $response = Http::post($url . 'api/product', $data);

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

            $reply = Http::get($url . 'api/product/' . $id);

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

            $requestProduct = $request->all();

            $data = [
                "added_by" => "pdv",
            ];

            if (isset($requestProduct['Product']['name'])) {
                $data["name"] = $requestProduct['Product']['name'];
                $data["slug"] = $requestProduct['Product']['name'];
            }

            if (isset($requestProduct['Product']['category_id'])) {
                $data["category_id"] = $requestProduct['Product']['category_id'];
            }

            if (isset($requestProduct['Product']['brand'])) {
                $data["brand_name"] = $requestProduct['Product']['brand'];
            }

            if (isset($requestProduct['Product']['weight'])) {
                $data["weight"] = $requestProduct['Product']['weight'];
            }

            if (isset($requestProduct['Product']['price'])) {
                $data["unit_price"] = $requestProduct['Product']['price'];
            }

            if (isset($requestProduct['Product']['description'])) {
                $data["description"] = $requestProduct['Product']['description'];
            }

            if (isset($requestProduct['Product']['stock'])) {
                $data["current_stock"] = $requestProduct['Product']['stock'];
            }

            $response = Http::put($url . 'api/product/' . $id, $data);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $url = config('enjoy.url');

            $response = Http::delete($url.'api/product/'.$id);

            return $response;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }
}
