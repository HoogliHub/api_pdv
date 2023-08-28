<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $data = [
            'sort' => [
                'field' => $request->query->get('sort') ?? 'id',
                'direction' => $request->query->get('order') ?? 'asc'
            ]
        ];

        $ordersQuery = DB::connection('enjoy')->table('orders as o')
            ->select('o.*')
            ->addSelect('od.tax')
            ->leftJoin('order_details as od', 'od.order_id', '=', 'o.id')
            ->leftJoin('combined_orders as co', 'co.id', '=', 'o.combined_order_id')
            ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
            ->orderBy('o.id' ?? 'o.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $ordersQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $ordersQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $orders = $ordersQuery->get();
        } else {
            $orders = $ordersQuery->paginate(10);
            $paging_data = [
                "total" => $orders->total(),
                "page" => $orders->currentPage(),
                "limit" => $orders->perPage(),
                "lastPage" => $orders->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $orders->map(function ($order) use (&$data) {
            $orderData = [
                'status_pagamento' => get_payment_status($order->payment_status),
                'status_entrega' => get_delivery_status($order->delivery_status),
                'id' => $order->id,
                'date' => date('Y-m-d', $order->date),
                'customer_id' => $order->user_id,
                'partial_total' => number_format($order->grand_total, 2),
                'taxes' => number_format($order->tax, 2),
                'discount' => number_format($order->coupon_discount, 2),
                'point_sale' => 'LOJA VIRTUAL',
                'shipment' => json_decode($order->shipping_address)->correios ?? null,
                'shipment_value' => number_format(json_decode($order->shipping_address)->valor_correios, 2) ?? 0.00
            ];
            $data['Orders'][] = $orderData;
        });

        return \response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
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
