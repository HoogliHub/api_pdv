<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Throwable;

class ColorsController extends Controller
{

    /**
     * @OA\Get(
     *      path="/api/colors",
     *      operationId="getColorsList",
     *      tags={"Colors"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Get list of colors",
     *      description="Returns a list of colors.",
     *      @OA\Parameter(
     *          name="sort",
     *          in="query",
     *          description="Sort field (e.g., id, name, code, created_at, updated_at)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="order",
     *          in="query",
     *          description="Sort order (asc or desc)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Parameter(
     *          name="limit",
     *          in="query",
     *          description="Limit the number of results (pagination)",
     *          @OA\Schema(type="integer", format="int32")
     *      ),
     *      @OA\Parameter(
     *          name="offset",
     *          in="query",
     *          description="Skip a certain number of results (pagination)",
     *          @OA\Schema(type="integer", format="int32")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="sort", type="object",
     *                      @OA\Property(property="field", type="string", example="id"),
     *                      @OA\Property(property="direction", type="string", example="asc")
     *                  ),
     *                  @OA\Property(property="fieldsAvailableSortBy", type="array",
     *                      @OA\Items(type="string", example="id"),
     *                      @OA\Items(type="string", example="name"),
     *                      @OA\Items(type="string", example="code"),
     *                      @OA\Items(type="string", example="created_at"),
     *                      @OA\Items(type="string", example="updated_at")
     *                  ),
     *                  @OA\Property(property="Colors", type="array",
     *                      @OA\Items(type="object",
     *                          @OA\Property(property="Color", type="object",
     *                              @OA\Property(property="id", type="integer", example=1),
     *                              @OA\Property(property="name", type="string", example="Red"),
     *                              @OA\Property(property="code", type="string", example="#FF0000"),
     *                              @OA\Property(property="display_name", type="string", example="Vibrant Red")
     *                          )
     *                      )
     *                  ),
     *                  @OA\Property(property="paging", type="object",
     *                      @OA\Property(property="total", type="integer", example=20),
     *                      @OA\Property(property="page", type="integer", example=1),
     *                      @OA\Property(property="limit", type="integer", example=10),
     *                      @OA\Property(property="lastPage", type="integer", example=2)
     *                  )
     *              )
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
                'code',
                'created_at',
                'updated_at',
            ]
        ];

        $colorsQuery = DB::connection('enjoy')->table('colors as c')
            ->orderBy('c.id' ?? 'c.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $colorsQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $colorsQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $colors = $colorsQuery->get();
        } else {
            $colors = $colorsQuery->paginate(10);
            $paging_data = [
                "total" => $colors->total(),
                "page" => $colors->currentPage(),
                "limit" => $colors->perPage(),
                "lastPage" => $colors->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $colors->map(function ($color) use (&$data) {
            $categoryData = [
                'Color' => [
                    'id' => $color->id,
                    'name' => $color->name,
                    'code' => $color->code,
                    'display_name' => $color->display_name
                ]
            ];
            $data['Colors'][] = $categoryData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *      path="/api/colors/create",
     *      operationId="createColor",
     *      tags={"Colors"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Create a new color",
     *      description="Creates a new color.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="Color", type="object",
     *                  @OA\Property(property="name", type="string", example="Red"),
     *                  @OA\Property(property="code", type="string", example="#FF0000"),
     *                  @OA\Property(property="display_name", type="string", example="Vibrant Red")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="code", type="integer", example=201),
     *              @OA\Property(property="status", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Color Created Successfully"),
     *              @OA\Property(property="color_id", type="integer", example=1)
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
     *                  @OA\Property(property="name", type="array",
     *                      @OA\Items(type="string", example="The name field is required.")
     *                  ),
     *                  @OA\Property(property="code", type="array",
     *                      @OA\Items(type="string", example="The code field is required.")
     *                  ),
     *                  @OA\Property(property="display_name", type="array",
     *                      @OA\Items(type="string", example="The display_name format is invalid.")
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
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input('Color'), [
            'name' => 'required|string',
            'code' => [
                'required',
                'string',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'display_name' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $color_exist = DB::connection('enjoy')->table('colors as c')
            ->where('c.code', '=', $request->input('Color.code'))
            ->first();

        if ($color_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'code' => [
                        'There is already a record with the given code: ' . $request->input('Color.code')
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $insert_color_data = [
                'name' => str_replace([' ', '_', '-', '.'], '', $request->input('Color.name')),
                'code' => $request->input('Color.code'),
            ];

            if ($request->has('Color.display_name')) {
                $insert_color_data['display_name'] = $request->input('Color.display_name');
            }

            $color = DB::connection('enjoy')->table('colors')->insertGetId($insert_color_data);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Color Created Successfully',
                'color_id' => $color
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
     *      path="/api/colors/{id}",
     *      operationId="getColorById",
     *      tags={"Colors"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Get color information",
     *      description="Retrieves color information based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the color",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=200),
     *              @OA\Property(property="data", type="object",
     *                  @OA\Property(property="Color", type="object",
     *                      @OA\Property(property="id", type="integer", example=1),
     *                      @OA\Property(property="name", type="string", example="Red"),
     *                      @OA\Property(property="code", type="string", example="#FF0000"),
     *                      @OA\Property(property="display_name", type="string", example="Vibrant Red")
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Data not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given code.")
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
            $color = DB::connection('enjoy')->table('colors as c')
                ->where('c.id', '=', $id)
                ->first();

            if ($color) {
                $data['Color'] = [
                    'id' => $color->id,
                    'name' => $color->name,
                    'code' => $color->code,
                    'display_name' => $color->display_name
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
                    'message' => 'There is no data for the given code.'
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
     *      path="/api/colors/{id}",
     *      operationId="updateColor",
     *      tags={"Colors"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Update color information",
     *      description="Updates color information based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the color",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="Color", type="object",
     *                  @OA\Property(property="name", type="string", example="Updated Red"),
     *                  @OA\Property(property="code", type="string", example="#FF5733"),
     *                  @OA\Property(property="display_name", type="string", example="Vibrant Red (Updated)")
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
     *              @OA\Property(property="message", type="string", example="Color Updated Successfully"),
     *              @OA\Property(property="color_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Data not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input data",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Validation error"),
     *              @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *          )
     *      )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->input('Color'), [
            'name' => 'string',
            'code' => [
                'string',
                'regex:/^#([a-f0-9]{6}|[a-f0-9]{3})$/i'
            ],
            'display_name' => 'string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $color_exist = DB::connection('enjoy')->table('colors as c')
            ->where('c.code', '=', $request->input('Color.code'))
            ->first();

        if ($color_exist) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'code' => [
                        'There is already a record with the given code: ' . $request->input('Color.code')
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $color = DB::connection('enjoy')->table('colors as c')
                ->where('c.id', '=', $id);

            if ($color) {
                $update_color_data = [
                    'updated_at' => now()
                ];
                if ($request->has('Color.name')) {
                    $update_color_data['name'] = str_replace([' ', '_', '-', '.'], '', $request->input('Color.name'));
                }
                if ($request->has('Color.code')) {
                    $update_color_data['code'] = $request->input('Color.code');
                }
                if ($request->has('Color.display_name')) {
                    $update_color_data['display_name'] = $request->input('Color.display_name');
                }

                $color->update($update_color_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Color Updated Successfully',
                    'color_id' => $color->first()->id
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
     *      path="/api/colors/{id}",
     *      operationId="deleteColor",
     *      tags={"Colors"},
     *      security={{ "bearerAuth": {} }},
     *      summary="Delete a color",
     *      description="Deletes a color based on the provided ID.",
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of the color",
     *          required=true,
     *          @OA\Schema(type="string", format="uuid")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=204),
     *              @OA\Property(property="message", type="string", example="Color deleted successfully")
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Data not found",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="status", type="integer", example=404),
     *              @OA\Property(property="data", type="object"),
     *              @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Invalid input data",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *          )
     *      )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $color = DB::connection('enjoy')->table('colors as c')
                ->where('c.id', '=', $id);

            if ($color) {
                $color->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Color deleted successfully'
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
