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

class ProductVariationController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/products/variants",
     *     summary="Get a list of product variations",
     *     tags={"Product Variations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field (e.g., id, product_id, variant, sku, price, qty, created_at, updated_at)",
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
     *         description="Limit the number of results",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Skip a number of results",
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of product variations",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="sort", type="object",
     *                     @OA\Property(property="field", type="string"),
     *                     @OA\Property(property="direction", type="string"),
     *                 ),
     *                 @OA\Property(property="fieldsAvailableSortBy", type="array", @OA\Items(type="string")),
     *                 @OA\Property(property="paging", type="object",
     *                     @OA\Property(property="total", type="integer"),
     *                     @OA\Property(property="page", type="integer"),
     *                     @OA\Property(property="limit", type="integer"),
     *                     @OA\Property(property="lastPage", type="integer"),
     *                 ),
     *                 @OA\Property(property="Variants", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="Variant", type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="product_id", type="integer"),
     *                             @OA\Property(property="price", type="string"),
     *                             @OA\Property(property="stock", type="integer"),
     *                             @OA\Property(property="minimum_stock", type="integer"),
     *                             @OA\Property(property="reference", type="string"),
     *                             @OA\Property(property="quantity_sold", type="integer"),
     *                             @OA\Property(property="Sku", type="array",
     *                                 @OA\Items(type="object",
     *                                     @OA\Property(property="type", type="string"),
     *                                     @OA\Property(property="value", type="string"),
     *                                 )
     *                             ),
     *                         ),
     *                     ),
     *                 ),
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
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
                'product_id',
                'variant',
                'sku',
                'price',
                'qty',
                'created_at',
                'updated_at',
            ]
        ];

        $variationsQuery = DB::connection('enjoy')->table('product_stocks as ps')
            ->select('ps.*')
            ->selectRaw("(select count(o.id) from order_details od left join orders o on od.order_id = o.id where o.payment_status = 'paid' and od.variation = ps.variant) as quantity_sold");

        if ($request->query->get('limit')) {
            $variationsQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $variationsQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $variations = $variationsQuery->get();
        } else {
            $variations = $variationsQuery->paginate(10);
            $paging_data = [
                "total" => $variations->total(),
                "page" => $variations->currentPage(),
                "limit" => $variations->perPage(),
                "lastPage" => $variations->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $variations->map(function ($variation) use (&$data) {
            $variationData = [
                'Variant' => [
                    'id' => $variation->id,
                    'product_id' => $variation->product_id,
                    'price' => number_format($variation->price, 2),
                    'stock' => $variation->qty,
                    'minimum_stock' => 1,
                    'reference' => $variation->sku,
                    'quantity_sold' => $variation->quantity_sold,
                    'Sku' => [
                        [
                            'type' => 'Cor',
                            'value' => explode('-', $variation->variant)[0] ?? ''
                        ],
                        [
                            'type' => 'Tamanho',
                            'value' => explode('-', $variation->variant)[1] ?? ''
                        ]
                    ]
                ]
            ];
            $data['Variants'][] = $variationData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/products/variants/create",
     *     summary="Create a new product variation",
     *     tags={"Product Variations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"Variant"},
     *             @OA\Property(property="Variant", type="object",
     *                 @OA\Property(property="product_id", type="integer"),
     *                 @OA\Property(property="price", type="string", format="float"),
     *                 @OA\Property(property="reference", type="string"),
     *                 @OA\Property(property="stock", type="integer"),
     *                 @OA\Property(property="type_1", type="string", enum={"Cor", "Tamanho", "Numeração"}),
     *                 @OA\Property(property="value_1", type="string"),
     *                 @OA\Property(property="type_2", type="string", enum={"Cor", "Tamanho", "Numeração"}),
     *                 @OA\Property(property="value_2", type="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product variation created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="code", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="variation_id", type="integer")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string"),
     *         ),
     *     ),
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $availableAttributes = DB::connection('enjoy')->table('attributes as a')->pluck('name')->toArray();

        $validator = Validator::make($request->input('Variant'), [
            'price' => 'required|decimal:2|min:0',
            'product_id' => 'required|integer',
            'reference' => 'required|string',
            'stock' => 'required|integer|min:0',
            'type_1' => 'string|in:Cor,' . implode(',', $availableAttributes),
            'value_1' => [
                'string',
                Rule::requiredIf($request->has('Variant.type_1'))
            ],
            'type_2' => 'string|in:Cor,' . implode(',', $availableAttributes),
            'value_2' => [
                'string',
                Rule::requiredIf($request->has('Variant.type_2'))
            ],
        ], [
            'type_1.in' => 'The :attribute field must have one of the following values: Cor, ' . implode(', ', $availableAttributes) . '.',
            'type_2.in' => 'The :attribute field must have one of the following values: Cor, ' . implode(', ', $availableAttributes) . '.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $errors_database = [];

        if ($request->has('Variant.type_1') && $request->has('Variant.type_2')) {
            if ($request->input('Variant.type_1') == 'Cor') {
                $color = DB::connection('enjoy')->table('colors as c')->where('c.name', '=', $request->input('Variant.value_1'))->first();
                $attribute = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_2'))->first();
                if (!$color) {
                    $errors_database['value_1'] = ['There is no data for the given color: ' . $request->input('Variant.value_1')];
                }
                if (!$attribute) {
                    $errors_database['value_2'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_2')];
                }
            } elseif ($request->input('Variant.type_2') == 'Cor') {
                $attribute = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_1'))->first();
                $color = DB::connection('enjoy')->table('colors as c')->where('c.name', '=', $request->input('Variant.value_2'))->first();
                if (!$attribute) {
                    $errors_database['value_1'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_1')];
                }
                if (!$color) {
                    $errors_database['value_2'] = ['There is no data for the given color: ' . $request->input('Variant.value_2')];
                }
            } else {
                $attribute1 = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_1'))->first();
                $attribute2 = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_2'))->first();
                if (!$attribute1) {
                    $errors_database['value_1'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_1')];
                }
                if (!$attribute2) {
                    $errors_database['value_2'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_2')];
                }
            }
        } else {
            if ($request->has('Variant.type_1')) {
                if ($request->input('Variant.type_1') == 'Cor') {
                    $color = DB::connection('enjoy')->table('colors as c')->where('c.name', '=', $request->input('Variant.value_1'))->first();
                    if (!$color) {
                        $errors_database['value_1'] = ['There is no data for the given color: ' . $request->input('Variant.value_1')];
                    }
                } else {
                    $attribute = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_1'))->first();
                    if (!$attribute) {
                        $errors_database['value_1'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_1')];
                    }
                }
            } else {
                if ($request->input('Variant.type_2') == 'Cor') {
                    $color = DB::connection('enjoy')->table('colors as c')->where('c.name', '=', $request->input('Variant.value_2'))->first();
                    if (!$color) {
                        $errors_database['value_2'] = ['There is no data for the given color: ' . $request->input('Variant.value_2')];
                    }
                } else {
                    $attribute = DB::connection('enjoy')->table('attribute_values as av')->where('av.value', '=', $request->input('Variant.value_2'))->first();
                    if (!$attribute) {
                        $errors_database['value_2'] = ['There is no data for the given attribute: ' . $request->input('Variant.value_2')];
                    }
                }
            }
        }

        if (!empty($errors_database)) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $errors_database
            ], 400);
        }

        try {
            DB::beginTransaction();

            $productId = $request->input('Variant.product_id');

            $product = DB::connection('enjoy')->table('products as p')
                ->where('p.id', '=', $productId)
                ->first();

            if (!$product) {
                return response()->json([
                    'success' => false,
                    'status' => 400,
                    'message' => 'Validation error',
                    'errors' => "There is no data for the given product_id: $productId"
                ], 400);
            }

            $hasValue1 = $request->has('Variant.value_1');
            $hasValue2 = $request->has('Variant.value_2');

            if ($hasValue1 && $hasValue2) {
                $type1 = $request->input('Variant.type_1');
                $type2 = $request->input('Variant.type_2');

                $variant = $type1 === 'Cor'
                    ? $request->input('Variant.value_1') . '-' . $request->input('Variant.value_2')
                    : $request->input('Variant.value_2') . '-' . $request->input('Variant.value_1');
            } elseif ($hasValue1) {
                $variant = $request->input('Variant.value_1');
            } elseif ($hasValue2) {
                $variant = $request->input('Variant.value_2');
            } else {
                return response()->json([
                    'success' => false,
                    'status' => 400,
                    'message' => 'Validation error',
                    'errors' => "At least one variation type must be given to create the product variation"
                ], 400);
            }

            $insertVariationData = [
                'product_id' => $productId,
                'variant' => $variant,
                'sku' => $request->input('Variant.reference'),
                'price' => (float)$request->input('Variant.price'),
                'qty' => $request->input('Variant.stock'),
            ];


            $update_product_variation_data = [];

            $get_attribute = function ($type, $value) {
                return DB::connection('enjoy')->table('attribute_values as av')
                    ->where('av.value', '=', $value)
                    ->first();
            };

            $process_color_or_attribute = function ($type, $value) use (&$update_product_variation_data, $get_attribute) {
                if ($type == 'Cor') {
                    $color = DB::connection('enjoy')->table('colors as c')
                        ->where('c.name', '=', $value)
                        ->first();
                    $update_product_variation_data['colors'] = json_encode([$color->code]);
                } else {
                    $attribute = $get_attribute($type, $value);
                    if (isset($update_product_variation_data['attributes'])) {
                        $attributes = json_decode($update_product_variation_data['attributes']);
                        $attributes[] = (string)$attribute->attribute_id;
                        $update_product_variation_data['attributes'] = json_encode($attributes);
                    } else {
                        $update_product_variation_data['attributes'] = json_encode([(string)$attribute->attribute_id]);
                    }
                    if (isset($update_product_variation_data['choice_options'])) {
                        $choice_options = json_decode($update_product_variation_data['choice_options']);
                        $choice_options[] = [
                            'attribute_id' => $attribute->attribute_id,
                            'values' => [$attribute->value]
                        ];
                        $update_product_variation_data['choice_options'] = json_encode($choice_options);
                    } else {
                        $update_product_variation_data['choice_options'] = json_encode([[
                            'attribute_id' => $attribute->attribute_id,
                            'values' => [$attribute->value]
                        ]]);
                    }
                }
            };

            if ($hasValue1) {
                $type1 = $request->input('Variant.type_1');
                $process_color_or_attribute($type1, $request->input('Variant.value_1'));
            }

            if ($hasValue2) {
                $type2 = $request->input('Variant.type_2');
                $process_color_or_attribute($type2, $request->input('Variant.value_2'));
            }

            $existing_data = [
                'attributes' => json_decode($product->attributes, true),
                'choice_options' => json_decode($product->choice_options, true),
                'colors' => json_decode($product->colors, true),
            ];

            if (isset($update_product_variation_data['attributes'])) {
                $attributes = json_decode($update_product_variation_data['attributes'], true);
                $existing_data['attributes'] = !empty($existing_data['attributes']) ? array_merge($existing_data['attributes'], $attributes) : $attributes;
            }

            if (isset($update_product_variation_data['choice_options'])) {
                $choice_options = json_decode($update_product_variation_data['choice_options'], true);
                $existing_data['choice_options'] = !empty($existing_data['choice_options']) ? array_merge($existing_data['choice_options'], $choice_options) : $choice_options;
            }

            if (isset($update_product_variation_data['colors'])) {
                $colors = json_decode($update_product_variation_data['colors'], true);
                $existing_data['colors'] = !empty($existing_data['colors']) ? array_merge($existing_data['colors'], $colors) : $colors;
            }

            $productStockId = DB::connection('enjoy')->table('product_stocks')->insertGetId($insertVariationData);

            DB::connection('enjoy')->table('products')->where('id', $productId)->update([
                'attributes' => json_encode($existing_data['attributes']),
                'choice_options' => json_encode($existing_data['choice_options']),
                'colors' => json_encode($existing_data['colors']),
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Product Variation Created Successfully',
                'variation_id' => $productStockId
            ], 201);
        } catch
        (Throwable $th) {
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
     *     path="/api/products/variants/{id}",
     *     summary="Get details of a product variation",
     *     tags={"Product Variations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product variation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product variation details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Variant", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="price", type="string", format="float"),
     *                     @OA\Property(property="stock", type="integer"),
     *                     @OA\Property(property="minimum_stock", type="integer"),
     *                     @OA\Property(property="reference", type="string"),
     *                     @OA\Property(property="quantity_sold", type="integer"),
     *                     @OA\Property(property="Sku", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="type", type="string"),
     *                             @OA\Property(property="value", type="string")
     *                         )
     *                     )
     *                 )
     *             ),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product variation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $variation = DB::connection('enjoy')->table('product_stocks as ps')
                ->select('ps.*')
                ->selectRaw("(select count(o.id) from order_details od left join orders o on od.order_id = o.id where o.payment_status = 'paid' and od.variation = ps.variant) as quantity_sold")
                ->where('ps.id', '=', $id)
                ->first();

            if ($variation) {
                $data['Variant'] = [
                    'id' => $variation->id,
                    'product_id' => $variation->product_id,
                    'price' => number_format($variation->price, 2),
                    'stock' => $variation->qty,
                    'minimum_stock' => 1,
                    'reference' => $variation->sku,
                    'quantity_sold' => $variation->quantity_sold,
                    'Sku' => [
                        [
                            'type' => 'Cor',
                            'value' => explode('-', $variation->variant)[0] ?? ''
                        ],
                        [
                            'type' => 'Tamanho',
                            'value' => explode('-', $variation->variant)[1] ?? ''
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
     * @OA\Put(
     *     path="/api/products/variants/{id}",
     *     summary="Update a product variation",
     *     tags={"Product Variations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product variation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="Variant", type="object",
     *                 @OA\Property(property="price", type="number", format="float", example=10.50),
     *                 @OA\Property(property="product_id", type="integer", example=1),
     *                 @OA\Property(property="reference", type="string", example="ABC123"),
     *                 @OA\Property(property="stock", type="integer", example=100),
     *                 @OA\Property(property="type_1", type="string", enum={"Cor", "Tamanho"}, example="Cor"),
     *                 @OA\Property(property="value_1", type="string", example="Red"),
     *                 @OA\Property(property="type_2", type="string", enum={"Cor", "Tamanho"}, example="Tamanho"),
     *                 @OA\Property(property="value_2", type="string", example="XL"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product variation updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="code", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="category_id", type="integer"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product variation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="error", type="string"),
     *         ),
     *     ),
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->input('Variant'), [
            'price' => 'decimal:2|min:0',
            'product_id' => 'integer',
            'reference' => 'string',
            'stock' => 'integer|min:0',
            'type_1' => 'string|in:Cor,Tamanho',
            'value_1' => 'string',
            'type_2' => 'string|in:Cor,Tamanho',
            'value_2' => 'string',
        ], [
            'type_1.in' => 'The :attribute field must be one of the following values: Cor or Tamanho.',
            'type_2.in' => 'The :attribute field must be one of the following values: Cor or Tamanho.'
        ]);

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

            $variation = DB::connection('enjoy')->table('product_stocks as ps')
                ->where('ps.id', '=', $id);

            if ($variation) {
                $update_variation_data = [
                    'updated_at' => now()
                ];

                if ($request->has('Variant.product_id')) {
                    $product = DB::connection('enjoy')->table('products as p')
                        ->where('p.id', '=', $request->input('Variant.product_id'))
                        ->first();

                    if ($product) {
                        $update_variation_data['product_id'] = $request->input('Variant.product_id');
                    } else {
                        return response()->json([
                            'success' => false,
                            'status' => 400,
                            'message' => 'Validation error',
                            'errors' => 'There is no data for the given product_id.'
                        ], 400);
                    }
                }

                if ($request->has('Variant.price')) {
                    $update_variation_data['price'] = (float)$request->input('Variant.price');
                }

                if ($request->has('Variant.reference')) {
                    $update_variation_data['sku'] = $request->input('Variant.reference');
                }

                if ($request->has('Variant.stock')) {
                    $update_variation_data['qty'] = $request->input('Variant.stock');
                }

                if ($request->filled(['Variant.value_1', 'Variant.value_2'])) {
                    $type1 = $request->input('Variant.type_1');
                    $type2 = $request->input('Variant.type_2');

                    $update_variation_data['variant'] = ($type1 === 'Cor')
                        ? $request->input('Variant.value_1') . '-' . $request->input('Variant.value_2')
                        : $request->input('Variant.value_2') . '-' . $request->input('Variant.value_1');
                } else {
                    $update_variation_data['variant'] = $request->filled('Variant.value_1')
                        ? $request->input('Variant.value_1')
                        : $request->input('Variant.value_2');
                }

                $variation->update($update_variation_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Category Updated Successfully',
                    'category_id' => $variation->first()->id
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
     *     path="/api/products/variants/{id}",
     *     summary="Delete a product variation",
     *     tags={"Product Variations"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product variation",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Product variation deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product variation not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="errors", type="object"),
     *         ),
     *     ),
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $variation = DB::connection('enjoy')->table('product_stocks as ps')
                ->where('ps.id', '=', $id)
                ->first();

            if (!$variation) {
                return response()->json([
                    'success' => true,
                    'status' => 404,
                    'data' => [],
                    'message' => 'There is no data for the given ID.'
                ], 404);
            }

            DB::connection('enjoy')->table('product_stocks')->where('id', '=', $variation->id)->delete();

            return response()->json([
                'success' => true,
                'status' => 204,
                'message' => 'Category deleted successfully'
            ], 200);
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
