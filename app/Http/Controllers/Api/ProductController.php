<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = ['sort' => [$request->query->get('sort') ?? 'id' => 'asc']];

        $productsQuery = DB::connection('enjoy')
            ->table('products')
            ->select('products.*')
            ->addSelect('b.name as brand_name')
            ->addSelect('c.name as category_name')
            ->leftJoin('brands as b', 'b.id', '=', 'products.brand_id')
            ->leftJoin('categories as c', 'c.id', '=', 'products.category_id')
            ->selectSub(function ($query) {
                $query->selectRaw('count(od.id)')
                    ->from('order_details as od')
                    ->where('od.payment_status', '=', 'paid')
                    ->whereRaw('od.product_id = products.id')
                    ->groupBy('od.product_id');
            }, 'quantity_sold')
            ->orderBy('products.id' ?? 'products.' . $request->query->get('sort'));

        if ($request->query->get('limit')) {
            $productsQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $productsQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $products = $productsQuery->get();
        } else {
            $products = $productsQuery->paginate(10);
            $paging_data = [
                "total" => $products->total(),
                "page" => $products->currentPage(),
                "limit" => $products->perPage(),
                "lastPage" => $products->lastPage()
            ];
            $data['paging'] = $paging_data;
        }
        $products->map(function ($product) use (&$data) {
            
            if ($product->discount_type === 'amount') {
                $promotionalPrice = $product->unit_price - $product->discount;
            } else {
                $discountedPrice = $product->unit_price * ($product->discount / 100);
                $promotionalPrice = $product->unit_price - ceil($discountedPrice);
            }

            $productData = [
                'modified' => $product->updated_at,
                'slug' => $product->slug,
                'id' => $product->id,
                'name' => $product->name,
                'price' => number_format($product->unit_price, '2'),
                'cost_price' => '0.00',
                'dollar_cost_price' => '0.00',
                'promotional_price' => number_format($promotionalPrice, 2),
                "start_promotion" => $product->discount_start_date ? date('y-m-d', $product->discount_start_date) : '0000-00-00',
                "end_promotion" => $product->discount_end_date ? date('y-m-d', $product->discount_end_date) : '0000-00-00',
                "brand" => $product->brand_id ? $product->brand_name : '',
                "brand_id" => $product->brand_id ? $product->brand_id : '',
                "model" => "",
                "weight" => $product->weight,
                "length" => "0",
                "width" => "0",
                "height" => "0",
                "stock" => $product->current_stock,
                "category_id" => $product->category_id,
                "category_name" => $product->category_name,
                "available" => $product->published === 1 ? '1' : '0',
                "availability" => "",
                "reference" => "",
                "hot" => "0",
                "release" => "0",
                "additional_button" => "0",
                "has_variation" => $product->variant_product ? '1' : '0',
                "rating" => '0',
                "count_rating" => $product->rating,
                "quantity_sold" => $product->quantity_sold,
                'url' => [
                    'http' => 'http://enjoylojas.com.br/produto/' . $product->slug,
                    'https' => 'https://enjoylojas.com.br/produto/' . $product->slug
                ],
                'created' => $product->created_at,
            ];
            $data['Products'][] = $productData;
        });

        return [
            'success' => true,
            'status' => 200,
            'data' => $data
        ];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        //
    }
}
