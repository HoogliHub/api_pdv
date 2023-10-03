<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Throwable;

class ProductAttributesController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/products/attributes",
     *      operationId="getAttributesList",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Get a list of attributes",
     *      description="Returns a list of attributes with optional pagination and sorting.",
     *      @OA\Parameter(
     *          name="sort",
     *          description="Sort field (e.g., id, name, created_at, updated_at)",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="id"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          description="Sort order (asc or desc)",
     *          in="query",
     *          @OA\Schema(
     *              type="string",
     *              default="asc"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          description="Limit the number of results",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="offset",
     *          description="Offset for pagination",
     *          in="query",
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="List of attributes",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="sort", type="object",
     *                      @OA\Property(property="field", type="string", example="id"),
     *                      @OA\Property(property="direction", type="string", example="asc")
     *                  ),
     *                  @OA\Property(property="fieldsAvailableSortBy", type="array",
     *                      @OA\Items(type="string", enum={"id", "name", "created_at", "updated_at"})
     *                  ),
     *                  @OA\Property(property="Attributes", type="array",
     *                      @OA\Items(
     *                          @OA\Property(property="Attribute", type="object",
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="name", type="string", example="Attribute Name")
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(property="paging", type="object",
     *                      @OA\Property(property="total", type="integer", example=100),
     *                      @OA\Property(property="page", type="integer", example=1),
     *                      @OA\Property(property="limit", type="integer", example=10),
     *                      @OA\Property(property="lastPage", type="integer", example=10)
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=500),
     *              @OA\Property(property="message", type="string", example="Internal server error"),
     *              @OA\Property(property="error", type="string", example="Error message")
     *          )
     *      )
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
                'created_at',
                'updated_at',
            ]
        ];

        $attributesQuery = DB::connection('enjoy')->table('attributes as a')
            ->orderBy('a.id' ?? 'a.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $attributesQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $attributesQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $attributes = $attributesQuery->get();
        } else {
            $attributes = $attributesQuery->paginate(10);
            $paging_data = [
                "total" => $attributes->total(),
                "page" => $attributes->currentPage(),
                "limit" => $attributes->perPage(),
                "lastPage" => $attributes->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $attributes->map(function ($attribute) use (&$data) {
            $attributeData = [
                'Attribute' => [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                ]
            ];
            $data['Attributes'][] = $attributeData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/products/attributes/create",
     *      operationId="createAttribute",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Create a new attribute",
     *      description="Creates a new attribute with the provided name and values.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="Attribute", type="object",
     *                  @OA\Property(property="name", type="string", example="Attribute Name"),
     *                  @OA\Property(property="values", type="array", @OA\Items(type="string", example="Value 1"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Attribute created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="code", type="integer", example=201),
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Attribute Created Successfully"),
     *              @OA\Property(property="attribute_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Validation error"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="name", type="array", @OA\Items(type="string", example="The name field is required")),
     *                  @OA\Property(property="values", type="array", @OA\Items(type="string", example="The values field is required"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=500),
     *              @OA\Property(property="message", type="string", example="Internal server error"),
     *              @OA\Property(property="error", type="string", example="Error message")
     *          )
     *      )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input('Attribute'), [
            'name' => 'required|string',
            'values' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $attribute_exist = DB::connection('enjoy')->table('attributes as a')
            ->where('a.name', 'LIKE', '%' . $request->input('Attribute.name') . '%')
            ->first();

        if ($attribute_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'name' => [
                        'There is already a record with the given name: ' . $request->input('Attribute.name')
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $insert_attribute_data = [
                'name' => $request->input('Attribute.name'),
            ];

            $attribute = DB::connection('enjoy')->table('attributes')->insertGetId($insert_attribute_data);

            foreach ($request->input('Attribute.values') as $value) {
                DB::connection('enjoy')->table('attribute_values')->insert([
                    'attribute_id' => $attribute,
                    'value' => $value
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Attribute Created Successfully',
                'attribute_id' => $attribute
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
     * @OA\Post(
     *      path="/api/products/attributes/values/create",
     *      operationId="createAttributeValue",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Create a new attribute value",
     *      description="Creates a new attribute value for a given attribute.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="AttributeValue", type="object",
     *                  @OA\Property(property="value", type="string", example="Attribute Value"),
     *                  @OA\Property(property="attribute_id", type="integer", example=1)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Attribute value created successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="code", type="integer", example=201),
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Attribute Value Created Successfully"),
     *              @OA\Property(property="attribute_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Validation error"),
     *              @OA\Property(property="errors", type="object",
     *                  @OA\Property(property="value", type="array", @OA\Items(type="string", example="The value field is required")),
     *                  @OA\Property(property="attribute_id", type="array", @OA\Items(type="string", example="The attribute_id field is required"))
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=500),
     *              @OA\Property(property="message", type="string", example="Internal server error"),
     *              @OA\Property(property="error", type="string", example="Error message")
     *          )
     *      )
     * )
     */
    public function store_values(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input('AttributeValue'), [
            'value' => 'required|string|integer',
            'attribute_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $attribute_exist = DB::connection('enjoy')->table('attributes as a')
            ->where('a.id', '=', $request->input('AttributeValue.attribute_id'))
            ->first();

        if (!$attribute_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'name' => [
                        'There is no record with the given attribute_id: ' . $request->input('AttributeValue.attribute_id')
                    ]
                ]
            ], 400);
        }

        $attribute_value_exist = DB::connection('enjoy')->table('attribute_values as av')
            ->where('av.attribute_id', '=', $request->input('AttributeValue.attribute_id'))
            ->where('av.value', '=', $request->input('AttributeValue.value'))
            ->first();

        if ($attribute_value_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'name' => [
                        'There is already a record with the data provided.'
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $attribute_value = DB::connection('enjoy')->table('attribute_values')
                ->insertGetId([
                    'value' => $request->input('AttributeValue.value'),
                    'attribute_id' => $request->input('AttributeValue.attribute_id')
                ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Attribute Value Created Successfully',
                'attribute_id' => $attribute_value
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
     *      path="/api/products/attributes/{id}",
     *      operationId="getAttributeById",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Get attribute by ID",
     *      description="Retrieves an attribute by its ID along with its associated values.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute to retrieve",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="Attribute", type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Attribute Name"),
     *                      @OA\Property(property="values", type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="value", type="string", example="Attribute Value")
     *                          )
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *          )
     *      )
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $attribute = DB::connection('enjoy')->table('attributes as a')
                ->where('a.id', '=', $id)
                ->first();
            $attribute_values = DB::connection('enjoy')->table('attribute_values as av')
                ->where('av.attribute_id', '=', $id)
                ->get();

            if ($attribute) {
                $data['Attribute'] = [
                    'id' => $attribute->id,
                    'name' => $attribute->name,
                    'values' => array_map(function ($attribute) {
                        return [
                            'id' => $attribute->id,
                            'value' => $attribute->value
                        ];
                    }, $attribute_values->all())
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
     *      path="/api/products/attributes/values/{id}",
     *      operationId="getAttributeValueById",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Get attribute value by ID",
     *      description="Retrieves an attribute value by its ID along with its associated attribute.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute value to retrieve",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="AttributeValue", type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="value", type="string", example="Attribute Value"),
     *                      @OA\Property(property="attribute", type="object",
     *                          @OA\Property(property="id", type="integer", example=1),
     *                          @OA\Property(property="name", type="string", example="Attribute Name")
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute value not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID supplied",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *          )
     *      )
     * )
     */
    public function show_values(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $attribute_values = DB::connection('enjoy')->table('attribute_values as av')
                ->select('av.*')
                ->addSelect('a.name')
                ->leftJoin('attributes as a', 'av.attribute_id', '=', 'a.id')
                ->where('av.id', '=', $id)
                ->first();

            if ($attribute_values) {
                $data['AttributeValue'] = [
                    'id' => $attribute_values->id,
                    'value' => $attribute_values->value,
                    'attribute' => [
                        'id' => $attribute_values->attribute_id,
                        'name' => $attribute_values->name
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
     *      path="/api/products/attributes/{id}",
     *      operationId="updateAttribute",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Update an existing attribute",
     *      description="Updates an existing attribute by ID along with its associated values.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute to update",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="Attribute", type="object",
     *                  @OA\Property(property="name", type="string", example="New Attribute Name"),
     *                  @OA\Property(property="values", type="array", @OA\Items(type="string"), example={"Value1", "Value2"})
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="code", type="integer", example=201),
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Attribute Updated Successfully"),
     *              @OA\Property(property="category_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID or request body",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Validation error"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=500),
     *              @OA\Property(property="message", type="string", example="Internal server error"),
     *              @OA\Property(property="error", type="string")
     *          )
     *      )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->input('Attribute'), [
            'name' => 'string',
            'values' => 'array',
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
            $attribute = DB::connection('enjoy')->table('attributes as a')
                ->where('a.id', '=', $id);
            if ($attribute) {
                $update_attribute_data = [
                    'updated_at' => now()
                ];
                if ($request->has('Attribute.name')) {
                    $update_attribute_data['name'] = $request->input('Attribute.name');
                }
                if ($request->has('Attribute.values')) {
                    DB::connection('enjoy')->table('attribute_values as av')
                        ->where('av.attribute_id', '=', $id)
                        ->delete();

                    foreach ($request->input('Attribute.values') as $value) {
                        DB::connection('enjoy')->table('attribute_values')->insert([
                            'attribute_id' => $id,
                            'value' => $value
                        ]);
                    }
                }

                $attribute->update($update_attribute_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Attribute Updated Successfully',
                    'category_id' => $attribute->first()->id
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
     * @OA\Put(
     *      path="/api/products/attributes/values/{id}",
     *      operationId="updateAttributeValue",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Update an existing attribute value",
     *      description="Updates an existing attribute value by ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute value to update",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="AttributeValue", type="object",
     *                  @OA\Property(property="value", type="string", example="New Attribute Value"),
     *                  @OA\Property(property="attribute_id", type="integer", example=1)
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="code", type="integer", example=201),
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Attribute Value Updated Successfully"),
     *              @OA\Property(property="category_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute value not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID or request body",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Validation error"),
     *              @OA\Property(property="errors", type="object")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal server error",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=500),
     *              @OA\Property(property="message", type="string", example="Internal server error"),
     *              @OA\Property(property="error", type="string")
     *          )
     *      )
     * )
     */
    public function update_values(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->input('AttributeValue'), [
            'attribute_id' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $attribute_exist = DB::connection('enjoy')->table('attributes as a')
            ->where('a.id', '=', $request->input('AttributeValue.attribute_id'))
            ->first();

        if (!$attribute_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'name' => [
                        'There is no record with the given attribute_id: ' . $request->input('AttributeValue.attribute_id')
                    ]
                ]
            ], 400);
        }

        $attribute_value_exist = DB::connection('enjoy')->table('attribute_values as av')
            ->where('av.attribute_id', '=', $request->input('AttributeValue.attribute_id'))
            ->where('av.value', '=', $request->input('AttributeValue.value'))
            ->first();

        if ($attribute_value_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'name' => [
                        'There is already a record with the data provided.'
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $attribute_value = DB::connection('enjoy')->table('attribute_values as av')
                ->where('av.id', '=', $id);

            if ($attribute_value) {
                $update_attribute_value_data = [
                    'updated_at' => now()
                ];

                if ($request->has('AttributeValue.attribute_id')) {
                    $update_attribute_value_data['attribute_id'] = $request->input('AttributeValue.attribute_id');
                }

                if ($request->has('AttributeValue.value')) {
                    $update_attribute_value_data['value'] = $request->input('AttributeValue.value');
                }

                $attribute_value->update($update_attribute_value_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Attribute Value Updated Successfully',
                    'category_id' => $attribute_value->first()->id
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
     *      path="/api/products/attributes/{id}",
     *      operationId="deleteAttribute",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Delete an attribute",
     *      description="Deletes an attribute by ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute to delete",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=204),
     *              @OA\Property(property="message", type="string", example="Attribute deleted successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *          )
     *      )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $attribute = DB::connection('enjoy')->table('attributes as a')
                ->where('a.id', '=', $id);

            if ($attribute) {
                $attribute->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Attribute deleted successfully'
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
     * @OA\Delete(
     *      path="/api/products/attributes/values/{id}",
     *      operationId="deleteAttributeValue",
     *      tags={"Attributes"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Delete an attribute value",
     *      description="Deletes an attribute value by ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the attribute value to delete",
     *          required=true,
     *          @OA\Schema(type="integer", format="int64")
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=204),
     *              @OA\Property(property="message", type="string", example="Attribute Value deleted successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Attribute Value not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid ID",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *          )
     *      )
     * )
     */
    public function destroy_values(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $attribute_value = DB::connection('enjoy')->table('attribute_values as av')
                ->where('av.id', '=', $id);

            if ($attribute_value) {
                $attribute_value->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Attribute Value deleted successfully'
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
