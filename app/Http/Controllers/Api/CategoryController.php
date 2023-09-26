<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnjoyUrlService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Throwable;

class CategoryController extends Controller
{
    private $urlService;

    public function __construct(EnjoyUrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * @OA\Get(
     *     path="/api/enjoy/categories",
     *     summary="Get a list of categories",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort field (e.g., id, name, parent_id, created_at, updated_at)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort direction (asc or desc)",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of items per page",
     *         @OA\Schema(type="integer", format="int32")
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Number of items to skip",
     *         @OA\Schema(type="integer", format="int32")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
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
     *                 @OA\Property(property="Categories", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="Category", type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="parent_id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="Images", type="object",
     *                                 @OA\Property(property="banner", type="object",
     *                                     @OA\Property(property="http", type="string"),
     *                                     @OA\Property(property="https", type="string"),
     *                                 ),
     *                                 @OA\Property(property="icon", type="object",
     *                                     @OA\Property(property="http", type="string"),
     *                                     @OA\Property(property="https", type="string"),
     *                                 ),
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
                'name',
                'parent_id',
                'created_at',
                'updated_at',
            ]
        ];

        $categoriesQuery = DB::connection('enjoy')->table('categories as c')
            ->select('c.id', 'c.parent_id', 'c.name')
            ->selectRaw('(select u.file_name from uploads u where u.id = c.banner) as banner')
            ->selectRaw('(select u.file_name from uploads u where u.id = c.icon) as icon')
            ->orderBy('c.id' ?? 'c.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $categoriesQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $categoriesQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $categories = $categoriesQuery->get();
        } else {
            $categories = $categoriesQuery->paginate(10);
            $paging_data = [
                "total" => $categories->total(),
                "page" => $categories->currentPage(),
                "limit" => $categories->perPage(),
                "lastPage" => $categories->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        $categories->map(function ($category) use (&$data) {
            $categoryData = [
                'Category' => [
                    'id' => $category->id,
                    'parent_id' => $category->parent_id,
                    'name' => $category->name,
                    'Images' => [
                        'banner' => generate_image_urls($category->banner, $this->urlService->getHttpUrl(), $this->urlService->getHttpsUrl()),
                        'icon' => generate_image_urls($category->icon, $this->urlService->getHttpUrl(), $this->urlService->getHttpsUrl())
                    ]
                ]
            ];
            $data['Categories'][] = $categoryData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/enjoy/categories/create",
     *     summary="Create a new category",
     *     tags={"Categories"},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="Category", type="object",
     *                 @OA\Property(property="is_featured", type="boolean"),
     *                 @OA\Property(property="metatag", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="title", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="parent_id", type="integer"),
     *                 @OA\Property(property="slug", type="string"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Category Created Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="code", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="category_id", type="integer")
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
        $validator = Validator::make($request->input('Category'), [
            'is_featured' => 'boolean',
            'metatag' => 'array',
            'metatag.*.description' => 'string',
            'metatag.*.title' => 'string',
            'name' => 'required|string',
            'parent_id' => 'integer',
            'slug' => 'required|string|unique:enjoy.categories'
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

            $insert_category_data = [
                'name' => $request->input('Category.name'),
                'slug' => $request->input('Category.slug'),
                'meta_title' => $request->input('Category.metatag.title'),
                'meta_description' => $request->input('Category.metatag.description'),
                'level' => $request->input('Category.parent_id') !== null and $request->input('Category.parent_id') == 0 ? 0 : 1,
                'featured' => !$request->input('Category.is_featured') ? 0 : 1
            ];

            if ($request->has('Category.parent_id')) {
                if ($request->input('Category.parent_id') > 0) {
                    $sub_category = DB::connection('enjoy')->table('categories as c')
                        ->where('c.id', '=', $request->input('Category.parent_id'))->first();
                    if ($sub_category) {
                        $update_category_data['parent_id'] = $request->input('Category.parent_id');
                        if ($request->input('Category.parent_id') >= 0) {
                            $insert_category_data['parent_id'] = $request->input('Category.parent_id');
                        }
                    } else {
                        return response()->json([
                            'success' => false,
                            'status' => 400,
                            'message' => 'Validation error',
                            'errors' => 'There is no data for the given parent_id.'
                        ], 400);
                    }
                }
            } else {
                $insert_category_data['parent_id'] = 0;
            }

            $category = DB::connection('enjoy')->table('categories')->insertGetId($insert_category_data);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Category Created Successfully',
                'category_id' => $category
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
     *     path="/api/enjoy/categories/show/{id}",
     *     summary="Get information about a specific category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category information retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Category", type="object",
     *                     @OA\Property(property="slug", type="string"),
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="parent_id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="order", type="integer"),
     *                     @OA\Property(property="link", type="object",
     *                         @OA\Property(property="http", type="string"),
     *                         @OA\Property(property="https", type="string")
     *                     ),
     *                     @OA\Property(property="metatag", type="object",
     *                         @OA\Property(property="meta_title", type="string"),
     *                         @OA\Property(property="meta_description", type="string")
     *                     ),
     *                     @OA\Property(property="Images", type="object",
     *                         @OA\Property(property="banner", type="string"),
     *                         @OA\Property(property="icon", type="string")
     *                     )
     *                 )
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string"),
     *         ),
     *     ),
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $category = DB::connection('enjoy')->table('categories as c')
                ->where('c.id', '=', $id)
                ->first();

            if ($category) {
                $data['Category'] = [
                    'slug' => $category->slug,
                    'id' => $category->id,
                    'parent_id' => $category->parent_id ?? '',
                    'name' => $category->name,
                    'order' => $category->order_level,
                    'link' => [
                        'http' => $this->urlService->getHttpUrl() . 'categoria/' . $category->name,
                        'https' => $this->urlService->getHttpsUrl() . 'categoria/' . $category->name,
                    ],
                    'metatag' => [
                        'meta_title' => $category->meta_title,
                        'meta_description' => $category->meta_description
                    ],
                    'Images' => [
                        'banner' => generate_image_urls($category->banner, $this->urlService->getHttpUrl(), $this->urlService->getHttpsUrl()),
                        'icon' => generate_image_urls($category->icon, $this->urlService->getHttpUrl(), $this->urlService->getHttpsUrl())
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
     * @OA\Get(
     *     path="/api/enjoy/categories/show/tree/{id}",
     *     summary="Show a category tree",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category tree retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Category", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="slug", type="string"),
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="parent_id", type="integer|null"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="order", type="integer"),
     *                         @OA\Property(property="link", type="object",
     *                             @OA\Property(property="http", type="string"),
     *                             @OA\Property(property="https", type="string"),
     *                         ),
     *                         @OA\Property(property="metatag", type="object",
     *                             @OA\Property(property="meta_title", type="string"),
     *                             @OA\Property(property="meta_description", type="string"),
     *                         ),
     *                         @OA\Property(property="Images", type="object",
     *                             @OA\Property(property="banner", type="string"),
     *                             @OA\Property(property="icon", type="string"),
     *                         ),
     *                         @OA\Property(property="has_product", type="integer"),
     *                         @OA\Property(property="children", type="object",
     *                 		   @OA\Property(property="Category", type="array",
     *                     				@OA\Items(
     *                         				@OA\Property(property="slug", type="string"),
     *                         				@OA\Property(property="id", type="integer"),
     *                         				@OA\Property(property="parent_id", type="integer|null"),
     *                         				@OA\Property(property="name", type="string"),
     *                         				@OA\Property(property="order", type="integer"),
     *                         				@OA\Property(property="link", type="object",
     *                             				@OA\Property(property="http", type="string"),
     *                             				@OA\Property(property="https", type="string"),
     *                         				),
     *                         				@OA\Property(property="metatag", type="object",
     *                             				@OA\Property(property="meta_title", type="string"),
     *                             				@OA\Property(property="meta_description", type="string"),
     *                         				),
     *                         				@OA\Property(property="Images", type="object",
     *                             				@OA\Property(property="banner", type="string"),
     *                             				@OA\Property(property="icon", type="string"),
     *                         				),
     *                         				@OA\Property(property="has_product", type="integer"),
     *                         				@OA\Property(property="children", type="array",
     *                                          @OA\Items()
     *                                      )
     *                     				)
     *                 		  )
     *                        )
     *                     )
     *                 )
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
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
    public function show_tree(string $id): JsonResponse
    {
        if (!is_numeric($id)) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'data' => [],
                'message' => 'The :id parameter must be of integer type.'
            ], 400);
        }

        $category = DB::connection('enjoy')->table('categories as c')
            ->select('c.*')
            ->selectRaw('exists(select * from products p where p.category_id = c.id) as has_product')
            ->where('c.id', '=', $id)
            ->first();

        if (!$category) {
            return response()->json([
                'success' => true,
                'status' => 404,
                'data' => [],
                'message' => 'There is no data for the given ID.'
            ], 404);
        }

        $category_data = $this->generate_category_array($category, $this->urlService);

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => ['Category' => [$category_data]]
        ], 200);
    }


    /**
     * @OA\Put(
     *     path="/api/enjoy/categories/update/{id}",
     *     summary="Update a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="Category", type="object",
     *                 @OA\Property(property="is_featured", type="boolean"),
     *                 @OA\Property(property="metatag", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="title", type="string")
     *                     )
     *                 ),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="parent_id", type="integer"),
     *                 @OA\Property(property="slug", type="string")
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="code", type="integer"),
     *             @OA\Property(property="status", type="boolean"),
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="category_id", type="integer")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
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
        $validator = Validator::make($request->input('Category'), [
            'is_featured' => 'boolean',
            'metatag' => 'array',
            'metatag.*.description' => 'string',
            'metatag.*.title' => 'string',
            'name' => 'string',
            'parent_id' => 'integer',
            'slug' => 'string|unique:enjoy.categories'
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

            $category = DB::connection('enjoy')->table('categories as c')
                ->where('c.id', '=', $id);

            if ($category) {
                $update_category_data = [
                    'updated_at' => now()
                ];

                if ($request->has('Category.parent_id')) {
                    if ($request->input('Category.parent_id') > 0) {
                        $sub_category = DB::connection('enjoy')->table('categories as c')
                            ->where('c.id', '=', $request->input('Category.parent_id'))->first();
                        if ($sub_category) {
                            $update_category_data['parent_id'] = $request->input('Category.parent_id');
                            if ($request->input('Category.parent_id') >= 0) {
                                $update_category_data['level'] = $request->input('Category.parent_id') == 0 ? 0 : 1;
                            }
                        } else {
                            return response()->json([
                                'success' => false,
                                'status' => 400,
                                'message' => 'Validation error',
                                'errors' => 'There is no data for the given parent_id.'
                            ], 400);
                        }
                    }
                }
                if ($request->has('Category.name')) {
                    $update_category_data['name'] = $request->input('Category.name');
                }
                if ($request->has('Category.slug')) {
                    $update_category_data['slug'] = $request->input('Category.slug');
                }
                if ($request->has('Category.metatag.title')) {
                    $update_category_data['meta_title'] = $request->input('Category.metatag.title');
                }
                if ($request->has('Category.metatag.description')) {
                    $update_category_data['meta_description'] = $request->input('Category.metatag.description');
                }
                if ($request->has('Category.is_featured')) {
                    $update_category_data['featured'] = $request->input('Category.is_featured');
                }

                $category->update($update_category_data);

                DB::commit();

                return response()->json([
                    'success' => true,
                    'code' => 201,
                    'status' => true,
                    'message' => 'Category Updated Successfully',
                    'category_id' => $category->first()->id
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
     *     path="/api/enjoy/categories/delete/{id}",
     *     summary="Delete a category",
     *     tags={"Categories"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the category",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Category deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="message", type="string")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Category not found",
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

            $category = DB::connection('enjoy')->table('categories as c')
                ->select('c.*')
                ->selectRaw('exists(select * from products p where p.category_id = c.id) as has_product')
                ->where('c.id', '=', $id)
                ->first();

            if ($category) {

                if ($category->has_product == 1) {
                    DB::connection('enjoy')->table('products')->where('category_id', '=', $category->id)->update([
                        'category_id' => 0,
                        'updated_at' => now()
                    ]);
                }

                $hasSubcategories = DB::connection('enjoy')->table('categories')
                    ->where('parent_id', $category->id)
                    ->exists();

                if ($hasSubcategories) {
                    DB::connection('enjoy')->table('categories')->where('parent_id', $category->id)->update([
                        'parent_id' => 0,
                        'updated_at' => now()
                    ]);
                }

                DB::connection('enjoy')->table('categories')->where('id', '=', $category->id)->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Category deleted successfully'
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

    function generate_category_array($category, $urlService): array
    {
        $category_array = [
            'Category' => [
                'slug' => $category->slug,
                'id' => $category->id,
                'parent_id' => $category->parent_id ?? null,
                'name' => $category->name,
                'order' => $category->order_level,
                'link' => [
                    'http' => $urlService->getHttpUrl() . 'categoria/' . $category->name,
                    'https' => $urlService->getHttpsUrl() . 'categoria/' . $category->name,
                ],
                'metatag' => [
                    'meta_title' => $category->meta_title,
                    'meta_description' => $category->meta_description
                ],
                'Images' => [
                    'banner' => generate_image_urls($category->banner, $urlService->getHttpUrl(), $urlService->getHttpsUrl()),
                    'icon' => generate_image_urls($category->icon, $urlService->getHttpUrl(), $urlService->getHttpsUrl())
                ],
                'has_product' => $category->has_product,
                'children' => []
            ]
        ];

        $subcategories = DB::connection('enjoy')->table('categories as c')
            ->select('c.*')
            ->selectRaw('exists(select * from products p where p.category_id = c.id) as has_product')
            ->where('c.id', '=', $category->parent_id)
            ->get();

        foreach ($subcategories as $subcategory) {
            $category_array['Category']['children'][] = $this->generate_category_array($subcategory, $urlService);
        }

        return $category_array;
    }

}
