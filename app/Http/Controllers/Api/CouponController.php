<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;
use Throwable;
use function Laravel\Prompts\select;

class CouponController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/coupons",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Get a paginated list of coupons",
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field (e.g., id, name, type, code, discount, discount_type, start_date, end_date, created_at, updated_at)",
     *         @OA\Schema(type="string"),
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort direction (asc or desc)",
     *         @OA\Schema(type="string", enum={"asc", "desc"}),
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit the number of results per page",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Skip a specific number of results",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of coupons",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="sort", type="object",
     *                     @OA\Property(property="field", type="string", example="id"),
     *                     @OA\Property(property="direction", type="string", example="asc"),
     *                 ),
     *                 @OA\Property(property="fieldsAvailableSortBy", type="array",
     *                     @OA\Items(type="string", example="id"),
     *                 ),
     *                 @OA\Property(property="paging", type="object",
     *                     @OA\Property(property="total", type="integer", example=10),
     *                     @OA\Property(property="page", type="integer", example=1),
     *                     @OA\Property(property="limit", type="integer", example=10),
     *                     @OA\Property(property="lastPage", type="integer", example=2),
     *                 ),
     *                 @OA\Property(property="DiscountCoupons", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="DiscountCoupon", type="object",
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="created", type="string", format="date-time", example="2023-09-28T14:36:41Z"),
     *                             @OA\Property(property="updated", type="string", format="date-time", example="2023-09-28T14:36:41Z"),
     *                             @OA\Property(property="code", type="string", example="ABC123"),
     *                             @OA\Property(property="details", type="object", additionalProperties=true, example={"key":"value"}),
     *                             @OA\Property(property="starts_at", type="string", format="date", example="2023-09-28"),
     *                             @OA\Property(property="ends_at", type="string", format="date", example="2023-09-30"),
     *                             @OA\Property(property="value", type="number", example=10.50),
     *                             @OA\Property(property="type", type="string", example="cart_base"),
     *                             @OA\Property(property="discount_type", type="string", example="$"),
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Invalid parameter"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="error", type="string", example="Error message.")
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
                'name',
                'type',
                'code',
                'discount',
                'discount_type',
                'start_date',
                'end_date',
                'created_at',
                'updated_at',
            ]
        ];

        $couponsQuery = DB::connection('enjoy')->table('coupons as c')
            ->orderBy('c.id' ?? 'c.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $couponsQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $couponsQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $coupons = $couponsQuery->get();
        } else {
            $coupons = $couponsQuery->paginate(10);
            $paging_data = [
                "total" => $coupons->total(),
                "page" => $coupons->currentPage(),
                "limit" => $coupons->perPage(),
                "lastPage" => $coupons->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $coupons->map(function ($coupon) use (&$data) {
            $couponData = [
                'DiscountCoupon' => [
                    'id' => $coupon->id,
                    'created' => $coupon->created_at,
                    'updated' => $coupon->updated_at,
                    'code' => $coupon->code,
                    'details' => json_decode($coupon->details),
                    'starts_at' => date('Y-m-d', $coupon->start_date),
                    'ends_at' => date('Y-m-d', $coupon->end_date),
                    'value' => $coupon->discount_type == 'amount' ? number_format($coupon->discount, 2) : $coupon->discount,
                    'type' => $coupon->type,
                    'discount_type' => $coupon->discount_type == 'amount' ? '$' : '%',
                ]
            ];
            $data['DiscountCoupons'][] = $couponData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/coupons",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Create a new coupon",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="DiscountCoupon", type="object",
     *                 @OA\Property(property="code", type="string", example="ABC123"),
     *                 @OA\Property(property="details", type="object", additionalProperties=true, example={"key":"value"}),
     *                 @OA\Property(property="discount", type="number", example=10.50),
     *                 @OA\Property(property="discount_type", type="string", example="amount"),
     *                 @OA\Property(property="end_date", type="string", format="date", example="2023-09-30"),
     *                 @OA\Property(property="start_date", type="string", format="date", example="2023-09-28"),
     *                 @OA\Property(property="type", type="string", example="cart_base")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coupon created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon Created Successfully"),
     *             @OA\Property(property="coupon_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object", additionalProperties=true, example={"field_name": {"Error message."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="error", type="string", example="Error message.")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input('DiscountCoupon'), [
            'code' => 'required|string',
            'details' => 'required|array',
            'discount' => ($request->input('DiscountCoupon.discount_type') == 'amount' ? 'required|decimal:2|min:1' : 'required|integer|min:1|max:100'),
            'discount_type' => 'required|string|in:amount,percent',
            'end_date' => 'required|date_format:Y-m-d|after_or_equal:start_date',
            'start_date' => 'required|date_format:Y-m-d|after_or_equal:' . date('Y-m-d'),
            'type' => 'required|string|in:cart_base,product_base'
        ]);

        $validator->sometimes('details.max_discount', 'required|decimal:2', function ($input) {
            return $input->type == 'cart_base';
        });

        $validator->sometimes('details.min_buy', 'required|integer', function ($input) {
            return $input->type == 'cart_base';
        });

        $validator->sometimes('details.product_id', 'required|array', function ($input) {
            return $input->type == 'product_base';
        });

        $validator->sometimes('details.product_id.*', 'integer', function ($input) {
            return $input->type == 'product_base';
        });


        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = DB::connection('enjoy')->table('users')->where('email', 'LIKE', '%' . $request->user()->email . '%')->first();
            $user_admin = DB::connection('enjoy')->table('users')->where('user_type', 'LIKE', '%' . 'admin' . '%')->first();

            $insert_coupon_data = [
                'code' => $request->input('DiscountCoupon.code'),
                'discount' => $request->input('DiscountCoupon.discount'),
                'discount_type' => $request->input('DiscountCoupon.discount_type'),
                'end_date' => strtotime($request->input('DiscountCoupon.end_date')),
                'start_date' => strtotime($request->input('DiscountCoupon.start_date')),
                'type' => $request->input('DiscountCoupon.type'),
                'user_id' => $user->id ?? $user_admin->id
            ];

            if ($request->has('DiscountCoupon.details.min_buy') || $request->has('DiscountCoupon.details.max_discount')) {
                $insert_coupon_data['details'] = json_encode([
                    'min_buy' => $request->input('DiscountCoupon.details.min_buy'),
                    'max_discount' => $request->input('DiscountCoupon.details.max_discount')
                ]);
            }

            if ($request->has('DiscountCoupon.details.product_id')) {
                $products = array_map(function ($item) {
                    return ['product_id' => $item];
                }, $request->input('DiscountCoupon.details.product_id'));

                $insert_coupon_data['details'] = json_encode($products);
            }

            $coupon = DB::connection('enjoy')->table('coupons')->insertGetId($insert_coupon_data);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Coupon Created Successfully',
                'coupon_id' => $coupon
            ], 201);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Internal server error',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/coupons/{id}",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Get details of a specific coupon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coupon to retrieve details from",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="DiscountCoupon", type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="created", type="string", format="date-time", example="2023-09-28T14:47:36Z"),
     *                     @OA\Property(property="updated", type="string", format="date-time", example="2023-09-28T14:47:36Z"),
     *                     @OA\Property(property="code", type="string", example="ABC123"),
     *                     @OA\Property(property="details", type="object", additionalProperties=true, example={"key":"value"}),
     *                     @OA\Property(property="starts_at", type="string", format="date", example="2023-09-28"),
     *                     @OA\Property(property="ends_at", type="string", format="date", example="2023-09-30"),
     *                     @OA\Property(property="value", type="number", example=10.50),
     *                     @OA\Property(property="type", type="string", example="cart_base"),
     *                     @OA\Property(property="discount_type", type="string", example="$"),
     *                     @OA\Property(property="total_number_of_users", type="integer", example=5),
     *                     @OA\Property(property="users", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=1),
     *                             @OA\Property(property="name", type="string", example="John Doe"),
     *                             @OA\Property(property="email", type="string", example="john@example.com"),
     *                             @OA\Property(property="cpf", type="string", example="12345678901")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $coupon = DB::connection('enjoy')->table('coupons as c')
                ->select('c.*')
                ->selectRaw('(select count(cu.id) from coupon_usages cu where cu.coupon_id = c.id) as total_number_of_users')
                ->where('c.id', '=', $id)
                ->first();

            $coupon_usage = DB::connection('enjoy')->table('coupon_usages as cu')
                ->select('cu.*')
                ->addSelect('u.name', 'u.email', 'u.cpf')
                ->leftJoin('users as u', 'cu.user_id', '=', 'u.id')
                ->where('cu.coupon_id', '=', $id)
                ->get();

            $users = $coupon_usage->map(function ($item) {
                return [
                    'id' => $item->user_id,
                    'name' => $item->name,
                    'email' => $item->email,
                    'cpf' => $item->cpf
                ];
            });

            if ($coupon) {
                $data['DiscountCoupon'] = [
                    'id' => $coupon->id,
                    'created' => $coupon->created_at,
                    'updated' => $coupon->updated_at,
                    'code' => $coupon->code,
                    'details' => json_decode($coupon->details),
                    'starts_at' => date('Y-m-d', $coupon->start_date),
                    'ends_at' => date('Y-m-d', $coupon->end_date),
                    'value' => $coupon->discount_type == 'amount' ? number_format($coupon->discount, 2) : $coupon->discount,
                    'type' => $coupon->type,
                    'discount_type' => $coupon->discount_type == 'amount' ? '$' : '%',
                    'total_number_of_users' => $coupon->total_number_of_users,
                    'users' => $users
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
     *     path="/api/coupons/{id}/products",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Get products associated with a specific coupon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coupon to retrieve products from",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Products retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="paging", type="object",
     *                     @OA\Property(property="total", type="integer", example=1),
     *                     @OA\Property(property="page", type="integer", example=1),
     *                     @OA\Property(property="limit", type="integer", example=10),
     *                     @OA\Property(property="lastPage", type="integer", example=1)
     *                 ),
     *                 @OA\Property(property="DiscountCouponProducts", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="DiscountCouponProduct", type="array",
     *                             @OA\Items(
     *                                 @OA\Property(property="product_id", type="string", example="4")
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *         )
     *     )
     * )
     */
    public function show_products(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $coupons = DB::connection('enjoy')->table('coupons as c')
                ->where('c.id', '=', $id)
                ->where('c.type', '=', 'product_base')
                ->paginate(10);

            if ($coupons) {
                $paging_data = [
                    "total" => $coupons->total(),
                    "page" => $coupons->currentPage(),
                    "limit" => $coupons->perPage(),
                    "lastPage" => $coupons->lastPage()
                ];
                $data['paging'] = $paging_data;

                $coupons->map(function ($coupon) use (&$data) {
                    $couponData = [
                        'DiscountCouponProduct' => [
                            array_map(function ($item) {
                                return $item;
                            }, json_decode($coupon->details))
                        ]
                    ];
                    $data['DiscountCouponProducts'][] = $couponData;
                });

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
     * @OA\Put(
     *     path="/api/coupons/{id}",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Update a specific coupon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coupon to be updated",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="DiscountCoupon", type="object",
     *                 @OA\Property(property="code", type="string"),
     *                 @OA\Property(property="details", type="object",
     *                     @OA\Property(property="max_discount", type="string"),
     *                     @OA\Property(property="min_buy", type="string")
     *                 ),
     *                 @OA\Property(property="discount", type="string"),
     *                 @OA\Property(property="discount_type", type="string", enum={"amount", "percent"}),
     *                 @OA\Property(property="end_date", type="string", format="date"),
     *                 @OA\Property(property="start_date", type="string", format="date"),
     *                 @OA\Property(property="type", type="string", enum={"cart_base", "product_base"})
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Coupon updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", example=201),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Coupon Updated Successfully"),
     *             @OA\Property(property="category_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object", example={
     *                 "code": {"The code field must be a string."},
     *                 "details": {"The details field must be an array."},
     *                 "discount": {"The discount field must be a decimal with 2 digits."},
     *                 "discount_type": {"The discount type field must be either 'amount' or 'percent'."},
     *                 "end_date": {"The end date field must be in the format 'Y-m-d' and after or equal to start date."},
     *                 "start_date": {"The start date field must be in the format 'Y-m-d' and after or equal to current date."},
     *                 "type": {"The type field must be either 'cart_base' or 'product_base'."}
     *             })
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="error", type="string", example="Error message here.")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->input('DiscountCoupon'), [
            'code' => 'string',
            'details' => 'array',
            'discount' => ($request->input('DiscountCoupon.discount_type') == 'amount' ? 'decimal:2|min:1' : 'integer|min:1|max:100'),
            'discount_type' => 'string|in:amount,percent',
            'end_date' => 'date_format:Y-m-d|after_or_equal:start_date',
            'start_date' => 'date_format:Y-m-d|after_or_equal:' . date('Y-m-d'),
            'type' => 'string|in:cart_base,product_base'
        ]);

        $validator->sometimes('details.max_discount', 'required|decimal:2', function ($input) {
            return $input->type == 'cart_base';
        });

        $validator->sometimes('details.min_buy', 'required|integer', function ($input) {
            return $input->type == 'cart_base';
        });

        $validator->sometimes('details.product_id', 'required|array', function ($input) {
            return $input->type == 'product_base';
        });

        $validator->sometimes('details.product_id.*', 'integer', function ($input) {
            return $input->type == 'product_base';
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            $coupon = DB::connection('enjoy')->table('coupons as c')
                ->where('c.id', '=', $id);

            if ($coupon) {
                $update_coupon_data = [
                    'updated_at' => now()
                ];

                if ($request->has('DiscountCoupon.code')) {
                    $update_coupon_data['code'] = $request->input('DiscountCoupon.code');
                }
                if ($request->has('DiscountCoupon.details')) {
                    if ($request->has('DiscountCoupon.details.min_buy') || $request->has('DiscountCoupon.details.max_discount')) {
                        $update_coupon_data['details'] = json_encode([
                            'min_buy' => $request->input('DiscountCoupon.details.min_buy'),
                            'max_discount' => $request->input('DiscountCoupon.details.max_discount')
                        ]);
                    }

                    if ($request->has('DiscountCoupon.details.product_id')) {
                        $products = array_map(function ($item) {
                            return ['product_id' => $item];
                        }, $request->input('DiscountCoupon.details.product_id'));

                        $update_coupon_data['details'] = json_encode($products);
                    }
                }
                if ($request->has('DiscountCoupon.discount')) {
                    $update_coupon_data['discount'] = $request->input('DiscountCoupon.discount');
                }
                if ($request->has('DiscountCoupon.discount_type')) {
                    $update_coupon_data['discount_type'] = $request->input('DiscountCoupon.discount_type');
                }
                if ($request->has('DiscountCoupon.end_date')) {
                    $update_coupon_data['end_date'] = strtotime($request->input('DiscountCoupon.end_date'));
                }
                if ($request->has('DiscountCoupon.start_date')) {
                    $update_coupon_data['start_date'] = strtotime($request->input('DiscountCoupon.start_date'));
                }
                if ($request->has('DiscountCoupon.type')) {
                    $update_coupon_data['type'] = $request->input('DiscountCoupon.type');
                }

                $coupon->update($update_coupon_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Coupon Updated Successfully',
                    'category_id' => $coupon->first()->id
                ], 201);
            } else {
                return response()->json([
                    'success' => true,
                    'status' => 404,
                    'data' => [],
                    'message' => 'There is no data for the given ID.'
                ], 404);
            }
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Internal server error',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/coupons/{id}",
     *     tags={"Coupons"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Remove a specific coupon",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the coupon to be deleted",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Coupon deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=204),
     *             @OA\Property(property="message", type="string", example="Coupon deleted successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Coupon not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="array", @OA\Items()),
     *             @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $coupon = DB::connection('enjoy')->table('coupons as c')
                ->where('c.id', '=', $id)->first();
            if ($coupon) {
                $coupon_usages = DB::connection('enjoy')->table('coupon_usages')->where('coupon_usages.coupon_id', '=', $id)->delete();

                DB::connection('enjoy')->table('coupons')->where('id', '=', $id)->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Coupon deleted successfully'
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
}
