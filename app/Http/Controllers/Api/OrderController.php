<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use OpenApi\Annotations as OA;
use function response;

class OrderController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/enjoy/orders",
     *     operationId="getOrders",
     *     tags={"Orders"},
     *     summary="Get a list of orders",
     *     description="Retrieve a list of orders with optional sorting, pagination, and filtering.",
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="Sort orders by a specific field",
     *          @OA\Schema(type="string",enum={"id", "delivery_status", "payment_type", "payment_status", "grand_total", "date", "created_at", "updated_at"})
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sorting order (asc or desc)",
     *          @OA\Schema(type="string", enum={"asc", "desc"})
     *      ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items to retrieve per page",
     *         @OA\Schema(type="integer",example="5")
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Number of items to skip",
     *         @OA\Schema(type="integer",example="10")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response with a list of orders",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="sort", type="object", @OA\Property(property="field", type="string",example="delivery_status"), @OA\Property(property="direction", type="string",example="desc")),
     *                 @OA\Property(property="fieldsAvailableSortBy", type="array",
     *                      @OA\Items(type="string",example="delivery_status"),
     *                      @OA\Items(type="string",example="payment_status"),
     *                      @OA\Items(type="string",example="payment_type"),
     *                      @OA\Items(type="string",example="grand_total"),
     *                      @OA\Items(type="string",example="date"),
     *                 ),
     *                 @OA\Property(property="paging", type="object", @OA\Property(property="total", type="integer"), @OA\Property(property="page", type="integer"), @OA\Property(property="limit", type="integer"), @OA\Property(property="lastPage", type="integer")),
     *                 @OA\Property(
     *                     property="Orders",
     *                     type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="status_pagamento", type="string",example="PENDENTE DE PAGAMENTO"),
     *                         @OA\Property(property="status_entrega", type="string",example="A ENVIAR"),
     *                         @OA\Property(property="id", type="integer",example="1"),
     *                         @OA\Property(property="date", type="string",example="0000-00-00"),
     *                         @OA\Property(property="customer_id", type="integer",example="2"),
     *                         @OA\Property(property="partial_total", type="string",example="655.51"),
     *                         @OA\Property(property="taxes", type="string",example="0.00"),
     *                         @OA\Property(property="discount", type="string",example="0.00"),
     *                         @OA\Property(property="point_sale", type="string",example="545.51"),
     *                         @OA\Property(property="shipment", type="string",example="SEDEX"),
     *                         @OA\Property(property="shipment_value", type="string",example="12.54"),
     *                         @OA\Property(property="value_1", type="string",example="456.45"),
     *                         @OA\Property(property="payment_type", type="string",example="rede"),
     *                         @OA\Property(property="payment_form", type="string",example="Cartão de Crédito"),
     *                         @OA\Property(property="total", type="string",example="546.45"),
     *                         @OA\Property(property="payment_date", type="string",example="0000-00-00"),
     *                         @OA\Property(property="modified", type="string",example="0000-00-00 00:00:00"),
     *                         @OA\Property(property="has_payment", type="integer",example="1"),
     *                         @OA\Property(property="has_shipment", type="integer",example="0"),
     *                         @OA\Property(property="ProductsSold", type="object",
     *                              @OA\Property(property="id", type="integer",example="3")
     *                         ),
     *                     )
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $data = [
            'sort' => [
                'field' => $request->query->get('sort') ?? 'id',
                'direction' => $request->query->get('order') ?? 'asc'
            ],
            'fieldsAvailableSortBy' => [
                'id',
                'delivery_status',
                'payment_type',
                'payment_status',
                'grand_total',
                'date',
                'created_at',
                'updated_at',
            ]
        ];

        $ordersQuery = DB::connection('enjoy')->table('orders as o')
            ->select('o.*')
            ->addSelect('od.tax', 'od.price', 'od.product_id')
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
                'partial_total' => number_format($order->price, 2),
                'taxes' => number_format($order->tax, 2),
                'discount' => number_format($order->coupon_discount, 2),
                'point_sale' => 'LOJA VIRTUAL',
                'shipment' => json_decode($order->shipping_address)->correios ?? null,
                'shipment_value' => number_format(json_decode($order->shipping_address)->valor_correios, 2) ?? 0.00,
                'value_1' => number_format($order->grand_total, 2),
                'payment_type' => $order->payment_type,
                'payment_form' => get_card_type(json_decode($order->payment_details)->card_type ?? ''),
                'total' => number_format($order->grand_total, 2),
                'payment_date' => json_decode($order->payment_details) ? date('Y-m-d', strtotime(json_decode($order->payment_details)->dateTime)) : '0000-00-00',
                'modified' => $order->updated_at,
                'has_payment' => $order->payment_status == 'paid' ? 1 : 0,
                'has_shipment' => json_decode($order->shipping_address)->correios ? 1 : 0,
                'ProductsSold' => [
                    'id' => $order->product_id
                ]
            ];
            $data['Orders'][] = $orderData;
        });

        return response()->json([
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
     * @OA\Get(
     *     path="/api/enjoy/orders/show/{id}",
     *     summary="Retrieve order details by ID",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to retrieve",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Order details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Order", type="object",
     *                     @OA\Property(property="status", type="string"),
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="date", type="string", format="date"),
     *                     @OA\Property(property="customer_id", type="integer"),
     *                     @OA\Property(property="partial_total", type="string"),
     *                     @OA\Property(property="taxes", type="string"),
     *                     @OA\Property(property="discount", type="string"),
     *                     @OA\Property(property="point_sale", type="string"),
     *                     @OA\Property(property="shipment", type="string"),
     *                     @OA\Property(property="shipment_value", type="string"),
     *                     @OA\Property(property="delivered", type="integer"),
     *                     @OA\Property(property="shipping_cancelled", type="integer"),
     *                     @OA\Property(property="discount_coupon", type="string"),
     *                     @OA\Property(property="installment", type="string"),
     *                     @OA\Property(property="value_1", type="string"),
     *                     @OA\Property(property="sending_code", type="string"),
     *                     @OA\Property(property="billing_address", type="string"),
     *                     @OA\Property(property="payment_method_id", type="integer"),
     *                     @OA\Property(property="payment_method", type="string"),
     *                     @OA\Property(property="total", type="string"),
     *                     @OA\Property(property="payment_date", type="string", format="date"),
     *                     @OA\Property(property="shipment_integrator", type="string"),
     *                     @OA\Property(property="modified", type="string", format="date-time"),
     *                     @OA\Property(property="is_traceable", type="integer"),
     *                     @OA\Property(property="tracking_url", type="string"),
     *                     @OA\Property(property="has_payment", type="integer"),
     *                     @OA\Property(property="has_shipment", type="integer"),
     *                     @OA\Property(property="ProductsSold", type="object",
     *                         @OA\Property(property="id", type="integer")
     *                     ),
     *                     @OA\Property(property="Payment", type="string")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Order not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $order = DB::connection('enjoy')->table('orders as o')
                ->select('o.*')
                ->addSelect('od.tax', 'od.price', 'od.product_id', 'od.shipping_cost')
                ->leftJoin('order_details as od', 'od.order_id', '=', 'o.id')
                ->leftJoin('combined_orders as co', 'co.id', '=', 'o.combined_order_id')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->where('o.id', '=', $id)
                ->first();

            if ($order) {
                $shipping_address = json_decode($order->shipping_address);

                $propertiesToRemove = ["name", "email", "correios", "valor_correios"];
                foreach ($propertiesToRemove as $property) {
                    unset($shipping_address->$property);
                }

                $settings = DB::connection('enjoy')->table('business_settings as bs')
                    ->where('type', '=', $order->payment_type)
                    ->first();

                $data['Order'] = [
                    'status' => get_payment_status($order->payment_status),
                    'id' => $order->id,
                    'date' => date('Y-m-d', $order->date),
                    'customer_id' => $order->user_id,
                    'partial_total' => number_format($order->price, 2),
                    'taxes' => number_format($order->tax, 2),
                    'discount' => number_format($order->coupon_discount, 2),
                    'point_sale' => 'LOJA VIRTUAL',
                    'shipment' => json_decode($order->shipping_address)->correios ?? null,
                    'shipment_value' => $order->shipping_cost,
                    'delivered' => $order->delivery_status == 'delivered' ? 1 : 0,
                    'shipping_cancelled' => $order->delivery_status == 'cancelled' ? 1 : 0,
                    'discount_coupon' => number_format($order->coupon_discount, 2),
                    'installment' => '1',
                    'value_1' => number_format($order->grand_total, 2),
                    'sending_code' => $order->tracking_code ?? null,
                    'billing_address' => json_encode($shipping_address),
                    'payment_method_id' => $settings ? $settings->id : null,
                    'payment_method' => $order->payment_type,
                    'total' => number_format($order->grand_total, 2),
                    'payment_date' => json_decode($order->payment_details) ? date('Y-m-d', strtotime(json_decode($order->payment_details)->dateTime)) : '0000-00-00',
                    'shipment_integrator' => 'Melhor Envio',
                    'modified' => $order->updated_at,
                    'is_traceable' => $order->tracking_code ? 1 : 0,
                    'tracking_url' => '',
                    'has_payment' => $order->payment_status == 'paid' ? 1 : 0,
                    'has_shipment' => json_decode($order->shipping_address)->correios ? 1 : 0,
                    'ProductsSold' => [
                        'id' => $order->product_id
                    ],
                    'Payment' => $order->payment_details
                ];

                return response()->json([
                    'success' => true,
                    'status' => 200,
                    'data' => $data
                ], 200);
            } else {
                return response()->json([
                    'success' => true,
                    'status' => 404,
                    'data' => [],
                    'message' => 'There is no data for the given ID.'
                ], 404);
            }
        } else {
            return response()->json([
                'success' => false,
                'status' => 400,
                'data' => [],
                'message' => 'The :id parameter must be of integer type.'
            ], 400);
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
        //
    }
}
