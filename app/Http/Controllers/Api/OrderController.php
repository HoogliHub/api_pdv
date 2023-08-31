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
                    'payment_method_id' => $settings?->id,
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
     * @OA\Get(
     *     path="/api/enjoy/orders/show/{order}/complete",
     *     summary="Retrieve detailed order information by ID",
     *     tags={"Orders"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the order to retrieve details for",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detailed order information retrieved successfully",
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
     *                     @OA\Property(property="Customer", type="object",
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="cpf", type="string"),
     *                         @OA\Property(property="email", type="string"),
     *                         @OA\Property(property="phone", type="string"),
     *                         @OA\Property(property="address", type="string"),
     *                         @OA\Property(property="zip_code", type="string"),
     *                         @OA\Property(property="state", type="string"),
     *                         @OA\Property(property="city", type="string"),
     *                         @OA\Property(property="country", type="string"),
     *                         @OA\Property(property="created", type="string", format="date-time"),
     *                         @OA\Property(property="modified", type="string", format="date-time"),
     *                         @OA\Property(property="Extensions", type="object",
     *                             @OA\Property(property="profile", type="string")
     *                         ),
     *                         @OA\Property(property="CustomerAddress", type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="customer_id", type="integer"),
     *                             @OA\Property(property="address", type="string"),
     *                             @OA\Property(property="zip_code", type="string"),
     *                             @OA\Property(property="state", type="string"),
     *                             @OA\Property(property="city", type="string"),
     *                             @OA\Property(property="country", type="string"),
     *                             @OA\Property(property="latitude", type="string"),
     *                             @OA\Property(property="longitude", type="string"),
     *                             @OA\Property(property="default_address", type="string"),
     *                         ),
     *                         @OA\Property(property="ProductsSold", type="object",
     *                             @OA\Property(property="product_id", type="integer"),
     *                             @OA\Property(property="quantity", type="integer"),
     *                             @OA\Property(property="order_id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="virtual_product", type="integer"),
     *                             @OA\Property(property="Sku", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="type", type="string"),
     *                                     @OA\Property(property="value", type="string")
     *                                 )
     *                             ),
     *                             @OA\Property(property="price", type="string"),
     *                             @OA\Property(property="reference", type="string"),
     *                             @OA\Property(property="weight", type="string"),
     *                             @OA\Property(property="variant_id", type="integer"),
     *                             @OA\Property(property="ProductSoldImage", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="http", type="string"),
     *                                     @OA\Property(property="https", type="string")
     *                                 )
     *                             ),
     *                             @OA\Property(property="Category", type="object",
     *                                 @OA\Property(property="id", type="integer"),
     *                                 @OA\Property(property="name", type="string"),
     *                                 @OA\Property(property="main_category_id", type="integer"),
     *                                 @OA\Property(property="main_category_name", type="string")
     *                             ),
     *                             @OA\Property(property="url", type="object",
     *                                 @OA\Property(property="http", type="string"),
     *                                 @OA\Property(property="https", type="string")
     *                             )
     *                         )
     *                     )
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
    public function show_details(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $baseHttpUrl = env('APP_ENV') === 'local' || env('APP_DEBUG') === true ? str_replace('https', 'http', env('ENJOY_URL_HOMOLOGATION')) : str_replace('https', 'http', env('ENJOY_URL_PRODUCTION'));
            $baseHttpsUrl = env('APP_ENV') === 'local' || env('APP_DEBUG') === true ? env('ENJOY_URL_HOMOLOGATION') : env('ENJOY_URL_PRODUCTION');

            $order = DB::connection('enjoy')->table('orders as o')
                ->select('o.*')
                ->addSelect('od.tax', 'od.price', 'od.product_id', 'od.shipping_cost', 'od.quantity as product_quantity', 'od.variation')
                ->addSelect('u.name as user_name', 'u.email as user_email', 'u.phone as user_phone', 'u.cpf as user_cpf', 'u.created_at as user_created_at', 'u.updated_at as user_modified', 'u.user_type')
                ->addSelect('a.id as address_id', 'a.address', 'a.postal_code as zip_code', 'c.name as country_name', 'c2.name as city_name', 's.name as state_name', 'a.set_default', 'a.latitude', 'a.longitude')
                ->addSelect('p.name as product_name', 'p.digital', 'p.unit_price', 'p.weight', 'p.photos as product_photos', 'p.slug as product_slug')
                ->addSelect('ps.sku', 'ps.id as variant_id')
                ->addSelect('c3.id as category_id', 'c3.name as category_name', 'c3.parent_id')
                ->selectRaw('(select name from categories where id = c3.parent_id) as main_category')
                ->leftJoin('order_details as od', 'od.order_id', '=', 'o.id')
                ->leftJoin('combined_orders as co', 'co.id', '=', 'o.combined_order_id')
                ->leftJoin('products as p', 'p.id', '=', 'od.product_id')
                ->leftJoin('product_stocks as ps', 'ps.product_id', '=', 'p.id')
                ->leftJoin('categories as c3', 'p.category_id', '=', 'c3.id')
                ->leftJoin('users as u', 'u.id', '=', 'o.user_id')
                ->leftJoin('addresses as a', 'o.user_id', '=', 'a.user_id')
                ->leftJoin('countries as c', 'a.country_id', '=', 'c.id')
                ->leftJoin('cities as c2', 'a.city_id', '=', 'c2.id')
                ->leftJoin('states as s', 'a.state_id', '=', 's.id')
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

                $photoIds = explode(',', $order->product_photos);
                $photos = DB::connection('enjoy')->table('uploads')->whereIn('id', $photoIds)->get();
                $photo_names = $photos->pluck('file_name')->toArray();

                $photosData = [];

                foreach ($photo_names as $photo) {
                    $photosInfo = [
                        'http' => $baseHttpUrl . 'public/' . $photo,
                        'https' => $baseHttpsUrl . 'public/' . $photo
                    ];
                    $photosData[] = $photosInfo;
                }

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
                    'payment_method_id' => $settings?->id,
                    'payment_method' => $order->payment_type,
                    'total' => number_format($order->grand_total, 2),
                    'payment_date' => json_decode($order->payment_details) ? date('Y-m-d', strtotime(json_decode($order->payment_details)->dateTime)) : '0000-00-00',
                    'shipment_integrator' => 'Melhor Envio',
                    'modified' => $order->updated_at,
                    'is_traceable' => $order->tracking_code ? 1 : 0,
                    'tracking_url' => '',
                    'has_payment' => $order->payment_status == 'paid' ? 1 : 0,
                    'has_shipment' => json_decode($order->shipping_address)->correios ? 1 : 0,
                    'Customer' => [
                        'id' => $order->user_id,
                        'name' => $order->user_name,
                        'cpf' => $order->user_cpf,
                        'email' => $order->user_email,
                        'phone' => $order->user_phone,
                        'address' => $order->address,
                        'zip_code' => $order->zip_code,
                        'state' => $order->state_name,
                        'city' => $order->city_name,
                        'country' => $order->country_name,
                        'created' => $order->user_created_at,
                        'modified' => $order->user_modified,
                        'Extensions' => [
                            'profile' => $order->user_type
                        ],
                        'CustomerAddress' => [
                            "id" => $order->address_id,
                            "customer_id" => $order->user_id,
                            'address' => $order->address,
                            'zip_code' => $order->zip_code,
                            'state' => $order->state_name,
                            'city' => $order->city_name,
                            'country' => $order->country_name,
                            'latitude' => $order->latitude,
                            'longitude' => $order->longitude,
                            'default_address' => $order->set_default
                        ],
                        'ProductsSold' => [
                            'product_id' => $order->product_id,
                            'quantity' => $order->product_quantity,
                            'order_id' => $order->id,
                            'name' => $order->product_name,
                            'virtual_product' => $order->digital,
                            'Sku' => [
                                [
                                    'type' => 'Cor',
                                    'value' => explode('-', $order->variation)[0]
                                ], [
                                    'type' => 'Tamanho',
                                    'value' => explode('-', $order->variation)[1]
                                ]
                            ],
                            'price' => number_format($order->unit_price, 2),
                            'reference' => is_numeric($order->sku) ? $order->sku : null,
                            'weight' => $order->weight,
                            'variant_id' => $order->variant_id,
                            'ProductSoldImage' => $photosData,
                            'Category' => [
                                'id' => $order->category_id,
                                'name' => $order->category_name,
                                'main_category_id' => $order->parent_id != 0 ? $order->parent_id : null,
                                'main_category_name' => $order->parent_id != 0 ? $order->main_category : null
                            ],
                            'url' => [
                                'http' => $baseHttpUrl . 'produto/' . $order->product_slug,
                                'https' => $baseHttpsUrl . 'produto/' . $order->product_slug,
                            ]
                        ]
                    ]
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
