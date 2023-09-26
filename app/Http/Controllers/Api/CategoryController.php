<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnjoyUrlService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CategoryController extends Controller
{
    private $urlService;

    public function __construct(EnjoyUrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * Display a listing of the resource.
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
     * Store a newly created resource in storage.
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

            $category = DB::connection('enjoy')->table('categories')->insertGetId([
                'parent_id' => $request->input('Category.parent_id', 0),
                'name' => $request->input('Category.name'),
                'slug' => $request->input('Category.slug'),
                'meta_title' => $request->input('Category.metatag.title'),
                'meta_description' => $request->input('Category.metatag.description'),
                'level' => $request->input('Category.parent_id') !== null and $request->input('Category.parent_id') == 0 ? 0 : 1,
                'featured' => !$request->input('Category.is_featured') ? 0 : 1
            ]);

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
     * Display the specified resource.
     */
    public function show(string $id)
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
