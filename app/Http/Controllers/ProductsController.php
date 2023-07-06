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
            $url = config('url');
            $reply = Http::get($url . '/api/product');

            $response = $reply->json();
            $data = [
                "paging" => $response['paging'],
                "id" => $response['id'],
                "name" => $response['name'],
                "description" => $response['description'],
                "content" => $response['content'],
                "status" => $response['status'],
                "sku" => $response['sku'],
                "quantity" => $response['quantity'],
                "featured" => $response['featured'],
                "price" => $response['price'],
                "sale_price" => $response['sale_price'],
                "lenght" => $response['lenght'],
                "wide" => $response['wide'],
                "height" => $response['height'],
                "weight" => $response['weight'],
                "updated_at" => $response['updated_at'],
                "slug" => $response['slug'],
                "unit_price" => $response['unit_price'],
                "purchase_price" => $response['purchase_price'],
                "total" => $response['total'],
                "discount_end_date" => $response['discount_end_date'],
                "cubic_weight" => $response,
                "stock" => $response['stock'],
                "category_id" => $response['category_id'],
                "published" => $response['published'],
                "thumbnail_img" => $response['thumbnail_img'],
                "atributes" => $response['atributes'],
                "colors" => $response['colors'],
                "min_qty" => $response['min_qty'],
                "low_stock_quantity" => $response['low_stock_quantity'],
                "meta_title" => $response['meta_title'],
                "meta_description" => $response['meta_description'],
                "video_link" => $response['video_link'],
                "variant_product" => $response['variant_product'],
                "variations" => $response['variations'],
                "colors" => $response['colors'],
                "tags" => $response['tags'],
                "attributes" => $response['attributes'],
                "choice_options" => $response['choice_options']
            ];

            return $data;
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
            $url = config('url');

            $data = [
                "id" => $request['id'],
                "name" => $request['name'],
                "description" => $request['description'],
                "content" => $request['content'],
                "status" => $request['status'],
                "sku" => $request['sku'],
                "quantity" => $request['quantity'],
                "featured" => $request['featured'],
                "price" => $request['price'],
                "sale_price" => $request['sale_price'],
                "lenght" => $request['lenght'],
                "wide" => $request['wide'],
                "height" => $request['height'],
                "weight" => $request['weight'],
                "updated_at" => $request['updated_at'],
                "slug" => $request['slug'],
                "unit_price" => $request['unit_price'],
                "purchase_price" => $request['purchase_price'],
                "total" => $request['total'],
                "discount_end_date" => $request['discount_end_date'],
                "cubic_weight" => $request,
                "stock" => $request['stock'],
                "category_id" => $request['category_id'],
                "published" => $request['published'],
                "thumbnail_img" => $request['thumbnail_img'],
                "atributes" => $request['atributes'],
                "colors" => $request['colors'],
                "min_qty" => $request['min_qty'],
                "low_stock_quantity" => $request['low_stock_quantity'],
                "meta_title" => $request['meta_title'],
                "meta_description" => $request['meta_description'],
                "video_link" => $request['video_link'],
                "variant_product" => $request['variant_product'],
                "variations" => $request['variations'],
                "colors" => $request['colors'],
                "tags" => $request['tags'],
                "attributes" => $request['attributes'],
                "choice_options" => $request['choice_options']
            ];
            $response = Http::post($url . '/api/product', $data);

            return $response->json();

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
            $url = config('url');
            $reply = Http::get($url . '/api/product/'.$id);

            $response = $reply->json();

            $data = [
                "id" => $response['id'],
                "name" => $response['name'],
                "description" => $response['description'],
                "content" => $response['content'],
                "status" => $response['status'],
                "sku" => $response['sku'],
                "quantity" => $response['quantity'],
                "featured" => $response['featured'],
                "price" => $response['price'],
                "sale_price" => $response['sale_price'],
                "lenght" => $response['lenght'],
                "wide" => $response['wide'],
                "height" => $response['height'],
                "weight" => $response['weight'],
                "updated_at" => $response['updated_at'],
                "slug" => $response['slug'],
                "unit_price" => $response['unit_price'],
                "purchase_price" => $response['purchase_price'],
                "total" => $response['total'],
                "discount_end_date" => $response['discount_end_date'],
                "cubic_weight" => $response,
                "stock" => $response['stock'],
                "category_id" => $response['category_id'],
                "published" => $response['published'],
                "thumbnail_img" => $response['thumbnail_img'],
                "atributes" => $response['atributes'],
                "colors" => $response['colors'],
                "min_qty" => $response['min_qty'],
                "low_stock_quantity" => $response['low_stock_quantity'],
                "meta_title" => $response['meta_title'],
                "meta_description" => $response['meta_description'],
                "video_link" => $response['video_link'],
                "variant_product" => $response['variant_product'],
                "variations" => $response['variations'],
                "colors" => $response['colors'],
                "tags" => $response['tags'],
                "attributes" => $response['attributes'],
                "choice_options" => $response['choice_options']
            ];

            return $data;

        } catch (\Exception $e) {
            throw new \Exception($e->getCode());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        try {
            $url = config('url');

            $data = [
                "id" => $request['id'],
                "name" => $request['name'],
                "description" => $request['description'],
                "content" => $request['content'],
                "status" => $request['status'],
                "sku" => $request['sku'],
                "quantity" => $request['quantity'],
                "featured" => $request['featured'],
                "price" => $request['price'],
                "sale_price" => $request['sale_price'],
                "lenght" => $request['lenght'],
                "wide" => $request['wide'],
                "height" => $request['height'],
                "weight" => $request['weight'],
                "updated_at" => $request['updated_at'],
                "slug" => $request['slug'],
                "unit_price" => $request['unit_price'],
                "purchase_price" => $request['purchase_price'],
                "total" => $request['total'],
                "discount_end_date" => $request['discount_end_date'],
                "cubic_weight" => $request,
                "stock" => $request['stock'],
                "category_id" => $request['category_id'],
                "published" => $request['published'],
                "thumbnail_img" => $request['thumbnail_img'],
                "atributes" => $request['atributes'],
                "colors" => $request['colors'],
                "min_qty" => $request['min_qty'],
                "low_stock_quantity" => $request['low_stock_quantity'],
                "meta_title" => $request['meta_title'],
                "meta_description" => $request['meta_description'],
                "video_link" => $request['video_link'],
                "variant_product" => $request['variant_product'],
                "variations" => $request['variations'],
                "colors" => $request['colors'],
                "tags" => $request['tags'],
                "attributes" => $request['attributes'],
                "choice_options" => $request['choice_options']
            ];
            $response = Http::post($url . '/api/product/'.$id, $data);

            return $response->json();

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
        $url = config('url');
        $response = Http::delete($url . '/api/product/'.$id);

        return $response->json();
    }
}
