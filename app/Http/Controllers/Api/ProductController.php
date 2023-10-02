<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnjoyUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use function response;

class ProductController extends Controller
{

    private $urlService;

    public function __construct(EnjoyUrlService $urlService)
    {
        $this->urlService = $urlService;
    }

    /**
     * @OA\Get(
     *     path="/api/products",
     *     operationId="getProducts",
     *     tags={"Products"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Get a list of products",
     *     description="Retrieve a list of products with optional sorting, pagination, and filtering.",
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort products by a specific field",
     *         required=false,
     *         @OA\Schema(type="string",enum={"id","name","unit_price","current_stock","created_at","updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Order products by a specific direction",
     *         required=false,
     *         @OA\Schema(type="string",enum={"asc","desc"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Limit the number of products per page",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Offset for pagination",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="integer", example="200"),
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *             @OA\Property(property="sort", type="object",
     *                  @OA\Property(property="field",type="string",example="id"),
     *                  @OA\Property(property="direction",type="string",example="asc")
     *              ),
     *                 @OA\Property(property="Products", type="object",
     *                     @OA\Property(property="modified", type="string", example="2018-12-26 13:46:12"),
     *                     @OA\Property(property="slug", type="string", example="produto-teste"),
     *                     @OA\Property(property="id", type="integer", example="0"),
     *                     @OA\Property(property="name", type="string", example="PRODUTO TESTE"),
     *                     @OA\Property(property="price", type="string", example="139.00"),
     *                     @OA\Property(property="cost_price",type="string",example="0.00"),
     *                     @OA\Property(property="dollar_cost_price",type="string",example="0.00"),
     *                     @OA\Property(property="promotional_price",type="string",example="0.00"),
     *                     @OA\Property(property="start_promotion",type="string",example="0000-00-00"),
     *                     @OA\Property(property="end_promotion",type="string",example="0000-00-00"),
     *                     @OA\Property(property="brand",type="string",example=""),
     *                     @OA\Property(property="brand_id",type="integer",example=""),
     *                     @OA\Property(property="model",type="string",example=""),
     *                     @OA\Property(property="weight",type="number",example="518"),
     *                     @OA\Property(property="length",type="string",example="0"),
     *                     @OA\Property(property="width",type="string",example="0"),
     *                     @OA\Property(property="height",type="string",example="0"),
     *                     @OA\Property(property="stock",type="integer",example="15"),
     *                     @OA\Property(property="category_id",type="integer",example="26"),
     *                     @OA\Property(property="category_name",type="string",example="Teste"),
     *                     @OA\Property(property="available",type="string",example="1"),
     *                     @OA\Property(property="availability",type="string",example=""),
     *                     @OA\Property(property="reference",type="string",example="645123"),
     *                     @OA\Property(property="hot",type="string",example="0"),
     *                     @OA\Property(property="release",type="string",example="0"),
     *                     @OA\Property(property="additional_button",type="string",example="0"),
     *                     @OA\Property(property="has_variation",type="string",example="1"),
     *                     @OA\Property(property="rating",type="string",example="0"),
     *                     @OA\Property(property="count_rating",type="integer",example="0"),
     *                     @OA\Property(property="quantity_sold",type="integer",example="0"),
     *                     @OA\Property(property="url",type="object",
     *                          @OA\Property(property="http",type="string",example="http://enjoy.com.br/produto-teste"),
     *                          @OA\Property(property="https",type="string",example="https://enjoy.com.br/produto-teste"),
     *                      ),
     *                     @OA\Property(property="created", type="string", example="2016-10-19 09:30:12"),
     *                     @OA\Property(property="Properties", type="object",
     *                          @OA\Property(property="tamanho",type="array",
     *                              @OA\Items(type="string",example="G")
     *                          ),
     *                          @OA\Property(property="cor",type="array",
     *                              @OA\Items(type="string",example="Preto")
     *                          ),
     *                     ),
     *                     @OA\Property(property="ProductImage",type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="http",type="string",example="https://enjoy.com.br/public/uploads/all/WLGOL2M6YGA3qoa.webp"),
     *                              @OA\Property(property="https",type="string",example="https://enjoy.com.br/public/uploads/all/WLGOL2M6YGA3qoa.webp")
     *                          )
     *                     ),
     *                     @OA\Property(property="Variant",type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="id",type="string",example="162")
     *                          )
     *                     )
     *                 ),
     *                 @OA\Property(property="paging", type="object",
     *                     @OA\Property(property="total", type="integer", example="1"),
     *                     @OA\Property(property="page", type="integer", example="1"),
     *                     @OA\Property(property="limit", type="integer", example="1"),
     *                     @OA\Property(property="lastPage", type="integer", example="1"),
     *                 )
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found"
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $data = [
            'sort' => [
                'field' => $request->query->get('sort') ?? 'id',
                'direction' => $request->query->get('order') ?? 'asc'
            ]
        ];

        $productsQuery = DB::connection('enjoy')
            ->table('products')
            ->select('products.*')
            ->addSelect('b.name as brand_name')
            ->addSelect('c.name as category_name')
            ->selectRaw('GROUP_CONCAT(ps.variant ORDER BY ps.variant) AS variants')
            ->selectRaw('GROUP_CONCAT(ps.id) AS variants_id')
            ->leftJoin('brands as b', 'b.id', '=', 'products.brand_id')
            ->leftJoin('categories as c', 'c.id', '=', 'products.category_id')
            ->leftJoin('product_stocks as ps', 'ps.product_id', '=', 'products.id')
            ->selectSub(function ($query) {
                $query->selectRaw('count(od.id)')
                    ->from('order_details as od')
                    ->where('od.payment_status', '=', 'paid')
                    ->whereRaw('od.product_id = products.id')
                    ->groupBy('od.product_id');
            }, 'quantity_sold')
            ->groupBy('products.id', 'products.name', 'b.name', 'c.name')
            ->orderBy('products.id' ?? 'products.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

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

        foreach ($products as $product) {
            $photoIds = explode(',', $product->photos);
            $photos = DB::connection('enjoy')->table('uploads')->whereIn('id', $photoIds)->get();
            $product->photo_names = $photos->pluck('file_name')->toArray();
        }

        $products->map(function ($product) use (&$data) {
            if ($product->discount_type === 'amount') {
                $promotionalPrice = $product->unit_price - $product->discount;
            } else {
                $discountedPrice = $product->unit_price * ($product->discount / 100);
                $promotionalPrice = $product->unit_price - ceil($discountedPrice);
            }

            $variants = explode(',', $product->variants);
            $variantData = [];

            foreach ($variants as $variant) {
                list($cor, $tamanho) = explode('-', $variant);
                $variantInfo = [
                    'tamanho' => [$tamanho],
                    'cor' => [$cor],
                ];
                $variantData[] = $variantInfo;
            }

            $photosData = [];

            foreach ($product->photo_names as $photo) {
                $photosInfo = [
                    'http' => $this->urlService->getHttpUrl() . 'public/' . $photo,
                    'https' => $this->urlService->getHttpsUrl() . 'public/' . $photo
                ];
                $photosData[] = $photosInfo;
            }

            $ids = explode(',', $product->variants_id);
            $variationIdData = [];

            foreach ($ids as $variant_id) {
                $variationIdInfo = [
                    'id' => $variant_id
                ];
                $variationIdData[] = $variationIdInfo;
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
                "start_promotion" => $product->discount_start_date ? date('Y-m-d', $product->discount_start_date) : '0000-00-00',
                "end_promotion" => $product->discount_end_date ? date('Y-m-d', $product->discount_end_date) : '0000-00-00',
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
                    'http' => $this->urlService->getHttpUrl() . 'produto/' . $product->slug,
                    'https' => $this->urlService->getHttpsUrl() . 'produto/' . $product->slug,
                ],
                'created' => $product->created_at,
                'Properties' => $variantData,
                'ProductImage' => $photosData,
                'Variant' => $variationIdData
            ];
            $data['Products'][] = $productData;
        });

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }


    /**
     * @OA\Post(
     *     path="/api/products/create",
     *     summary="Create a new product",
     *     tags={"Products"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="name", type="string", example="Nome do Produto"),
     *                 @OA\Property(property="category_id", type="integer", format="int64", example=1),
     *                 @OA\Property(property="unit", type="string", enum={"pc", "un"}, example="pc"),
     *                 @OA\Property(property="weight", type="integer", format="int32", example=500),
     *                 @OA\Property(property="min_qty", type="integer", format="int32", example=10),
     *                 @OA\Property(property="tags", type="array", @OA\Items(type="string"), example={"tag1", "tag2"}),
     *                 @OA\Property(property="barcode", type="string", example="123456789"),
     *                 @OA\Property(property="is_refundable", type="boolean", example=true),
     *                 @OA\Property(property="images", type="object",
     *                     @OA\Property(property="gallery", type="array", @OA\Items(type="string", format="uri"), example={"http://example.com/image1.jpg", "http://example.com/image2.jpg"}),
     *                     @OA\Property(property="miniature", type="string", format="uri", example="http://example.com/miniature.jpg"),
     *                 ),
     *                 @OA\Property(property="video", type="object",
     *                     @OA\Property(property="provider", type="string", enum={"youtube", "vimeo"}, example="youtube"),
     *                     @OA\Property(property="link", type="string", format="uri", example="http://example.com/video"),
     *                 ),
     *                 @OA\Property(property="unit_price", type="number", format="float", example=29.99),
     *                 @OA\Property(property="is_discounted", type="boolean", example=true),
     *                 @OA\Property(property="discount", type="object",
     *                     @OA\Property(property="type", type="string", enum={"amount", "percent"}, example="amount"),
     *                     @OA\Property(property="value", type="number", format="float", example=5.99),
     *                     @OA\Property(property="discount_start_date", type="string", format="date", example="2023-08-23"),
     *                     @OA\Property(property="discount_end_date", type="string", format="date", example="2023-09-23"),
     *                 ),
     *                 @OA\Property(property="current_stock", type="integer", format="int32", example=100),
     *                 @OA\Property(property="description", type="string", example="Descrição do produto"),
     *                 @OA\Property(property="metatag", type="object",
     *                     @OA\Property(property="title", type="string", example="Título da Metatag"),
     *                     @OA\Property(property="description", type="string", example="Descrição da Metatag"),
     *                     @OA\Property(property="image", type="string", format="uri", example="http://example.com/metatag.jpg"),
     *                 ),
     *                 @OA\Property(property="low_stock_quantity", type="integer", format="int32", example=20),
     *                 @OA\Property(property="is_featured", type="boolean", example=true),
     *                 @OA\Property(property="is_todays_deal", type="boolean", example=true),
     *                 @OA\Property(property="published", type="boolean", example=true),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product Created Successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="code", type="integer", format="int32", example=201),
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Produto criado com sucesso"),
     *             @OA\Property(property="category_id", type="integer", format="int64", example=123)
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", format="int32", example=400),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object", additionalProperties=@OA\Property(type="array", @OA\Items(type="string")))
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", format="int32", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error"),
     *             @OA\Property(property="error", type="string")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->input('Product'), [
            'name' => 'required|string',
            'category_id' => 'required|integer|min:1',
            'unit' => 'required|string|in:pc,un',
            'weight' => 'integer|min:0',
            'min_qty' => 'integer|min:1',
            'tags' => 'array',
            'barcode' => 'string',
            'is_refundable' => 'boolean',
            'images' => 'array|valid_keys:gallery,miniature',
            'images.miniature' => 'url:http,https',
            'images.gallery' => 'array|min:1',
            'images.gallery.*' => 'url:http,https',
            'video' => 'array|valid_keys:provider,link',
            'video.provider' => 'string|in:youtube,vimeo',
            'video.link' => 'url:http,https',
            'unit_price' => 'required|decimal:2',
            'is_discounted' => 'required|boolean',
            'discount' => [
                'array',
                'valid_keys:type,value,discount_start_date,discount_end_date',
                Rule::requiredIf($request->has('Product.is_discounted') == true)
            ],
            'discount.type' => 'string|in:amount,percent',
            'discount.value' => ($request->input('Product.discount.type') == 'amount' ? 'decimal:2|min:1' : 'integer|min:1|max:100'),
            'discount.discount_start_date' => 'date_format:Y-m-d|after_or_equal:' . date('Y-m-d'),
            'discount.discount_end_date' => 'date_format:Y-m-d|after_or_equal:discount.discount_start_date',
            'current_stock' => 'required|integer|min:1',
            'description' => 'string',
            'metatag' => 'array|valid_keys:title,description,image',
            'metatag.title' => 'string',
            'metatag.description' => 'string',
            'metatag.image' => 'url:http,https',
            'low_stock_quantity' => 'integer|min:1',
            'is_featured' => 'required|boolean',
            'is_todays_deal' => 'required|boolean',
            'published' => 'required|boolean',
        ], [
            'unit.in' => 'The :attribute field must be one of the following values: PC or UN',
            'video.provider.in' => 'The :attribute field must be one of the following values: Youtube or Vimeo',
            'discount.type.in' => 'The :attribute field must be one of the following values: Amount or Percent'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 400);
        }

        $category = DB::connection('enjoy')->table('categories as c')
            ->where('c.id', '=', $request->input('Product.category_id'))
            ->first();

        if (!$category) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Validation error',
                'errors' => [
                    'category_id' => [
                        'The specified category_id does not exist'
                    ]
                ]
            ], 400);
        }

        try {
            DB::beginTransaction();

            $user = DB::connection('enjoy')->table('users')->where('email', 'LIKE', '%' . $request->user()->email . '%')->first();

            $user_admin = DB::connection('enjoy')->table('users')->where('user_type', 'LIKE', '%' . 'admin' . '%')->first();

            $insert_product_data = [
                'name' => $request->input('Product.name'),
                'user_id' => $user->id ?? $user_admin->id,
                'category_id' => $request->input('Product.category_id'),
                'unit_price' => (float)$request->input('Product.unit_price'),
                'todays_deal' => $request->input('Product.is_todays_deal') ? 1 : 0,
                'published' => $request->input('Product.published') ? 1 : 0,
                'featured' => $request->input('Product.is_featured') ? 1 : 0,
                'current_stock' => (integer)$request->input('Product.current_stock'),
                'unit' => $request->input('Product.unit'),
                'slug' => str_replace([' ', '.', ','], '-', $request->input('Product.name')),
                'external_link_btn' => null
            ];

            if ($request->has('Product.images')) {
                $photos = [];

                if ($request->has('Product.images.gallery')) {
                    foreach ($request->input('Product.images.gallery') as $image) {
                        $image_id = DB::connection('enjoy')->table('uploads')->insertGetId([
                            'user_id' => $user->id ?? $user_admin->id,
                            'type' => 'image',
                            'external_link' => $image
                        ]);
                        $photos[] = $image_id;
                    }
                }

                if ($request->has('Product.images.miniature')) {
                    $image_id = DB::connection('enjoy')->table('uploads')->insertGetId([
                        'user_id' => $user->id ?? $user_admin->id,
                        'type' => 'image',
                        'external_link' => $request->input('Product.images.miniature')
                    ]);
                    $insert_product_data['thumbnail_img'] = $image_id;
                }

                if (!empty($photos)) {
                    $insert_product_data['photos'] = implode(',', $photos);
                }
            }

            if ($request->has('Product.video')) {
                if ($request->has('Product.video.provider')) {
                    $insert_product_data['video_provider'] = $request->input('Product.video.provider');
                }
                if ($request->has('Product.video.link')) {
                    $insert_product_data['video_link'] = $request->input('Product.video.link');
                }
            }

            if ($request->has('Product.tags')) {
                $insert_product_data['tags'] = implode(',', $request->input('Product.tags'));
            }

            if ($request->has('Product.description')) {
                $insert_product_data['description'] = $request->input('Product.description');
            }

            if ($request->has('Product.weight')) {
                $insert_product_data['weight'] = $request->input('Product.weight');
            }

            if ($request->has('Product.min_qty')) {
                $insert_product_data['min_qty'] = $request->input('Product.min_qty');
            }

            if ($request->has('Product.low_stock_quantity')) {
                $insert_product_data['low_stock_quantity'] = $request->input('Product.low_stock_quantity');
            }

            if ($request->has('Product.discount')) {
                $insert_product_data['discount_type'] = $request->input('Product.discount.type');
                $insert_product_data['discount'] = $request->input('Product.discount.value');
                $insert_product_data['discount_start_date'] = strtotime($request->input('Product.discount.discount_start_date'));
                $insert_product_data['discount_end_date'] = strtotime($request->input('Product.discount.discount_end_date'));
            }

            if ($request->has('Product.metatag')) {
                if ($request->has('Product.metatag.title')) {
                    $insert_product_data['meta_title'] = $request->input('Product.metatag.title');
                }
                if ($request->has('Product.metatag.description')) {
                    $insert_product_data['meta_description'] = $request->input('Product.metatag.description');
                }
                if ($request->has('Product.metatag.image')) {
                    $meta_image_id = DB::connection('enjoy')->table('uploads')->insertGetId([
                        'user_id' => $user->id ?? $user_admin->id,
                        'type' => 'image',
                        'external_link' => $request->input('Product.metatag.image')
                    ]);
                    $insert_product_data['meta_img'] = $meta_image_id;
                }
            }

            if ($request->has('Product.barcode')) {
                $insert_product_data['barcode'] = $request->input('Product.barcode');
            }

            if ($request->has('Product.is_refundable')) {
                $insert_product_data['refundable'] = $request->input('Product.is_refundable') ? 1 : 0;
            }

            $product = DB::connection('enjoy')->table('products')->insertGetId($insert_product_data);

            DB::commit();

            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'Product Created Successfully',
                'category_id' => $product
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
     *     path="/api/products/{id}",
     *     summary="Get product details by ID",
     *     tags={"Products"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Product ID"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="status", type="integer"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Product", type="object",
     *                         @OA\Property(property="modified", type="string"),
     *                         @OA\Property(property="slug", type="string"),
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="name", type="string"),
     *                         @OA\Property(property="description", type="string"),
     *                         @OA\Property(property="price", type="string"),
     *                         @OA\Property(property="cost_price", type="string"),
     *                         @OA\Property(property="dollar_cost_price", type="string"),
     *                         @OA\Property(property="promotional_price", type="string"),
     *                         @OA\Property(property="start_promotion", type="string"),
     *                         @OA\Property(property="end_promotion", type="string"),
     *                         @OA\Property(property="brand",type="string",example=""),
     *                         @OA\Property(property="brand_id",type="integer",example=""),
     *                         @OA\Property(property="model",type="string",example=""),
     *                         @OA\Property(property="weight",type="number",example="518"),
     *                         @OA\Property(property="length",type="string",example="0"),
     *                         @OA\Property(property="width",type="string",example="0"),
     *                         @OA\Property(property="height",type="string",example="0"),
     *                         @OA\Property(property="stock",type="integer",example="15"),
     *                         @OA\Property(property="category_id",type="integer",example="26"),
     *                         @OA\Property(property="category_name",type="string",example="Teste"),
     *                         @OA\Property(property="available",type="string",example="1"),
     *                         @OA\Property(property="availability",type="string",example=""),
     *                         @OA\Property(property="reference",type="string",example="645123"),
     *                         @OA\Property(property="hot",type="string",example="0"),
     *                         @OA\Property(property="release",type="string",example="0"),
     *                         @OA\Property(property="additional_button",type="string",example="0"),
     *                         @OA\Property(property="has_variation",type="string",example="1"),
     *                         @OA\Property(property="has_acceptance_terms",type="string",example="1"),
     *                         @OA\Property(property="has_buy_together",type="string",example="1"),
     *                         @OA\Property(property="additional_message",type="string",example="Informação Adicional 3"),
     *                         @OA\Property(property="warranty",type="string",example=""),
     *                         @OA\Property(property="rating",type="string",example="0"),
     *                         @OA\Property(property="count_rating",type="integer",example="0"),
     *                         @OA\Property(property="quantity_sold",type="integer",example="0"),
     *                         @OA\Property(property="ProductImage",type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="http",type="string",example="https://enjoy.com.br/public/uploads/all/WLGOL2M6YGA3qoa.webp"),
     *                               @OA\Property(property="https",type="string",example="https://enjoy.com.br/public/uploads/all/WLGOL2M6YGA3qoa.webp")
     *                           )
     *                         ),
     *                         @OA\Property(property="image", type="string"),
     *                         @OA\Property(property="url", type="object",
     *                             @OA\Property(property="http", type="string"),
     *                             @OA\Property(property="https", type="string")
     *                         ),
     *                         @OA\Property(property="created", type="string", example="2016-10-19 09:30:12"),
     *                         @OA\Property(property="Properties", type="object",
     *                           @OA\Property(property="tamanho",type="array",
     *                               @OA\Items(type="string",example="G")
     *                           ),
     *                           @OA\Property(property="cor",type="array",
     *                               @OA\Items(type="string",example="Preto")
     *                           ),
     *                        ),
     *                        @OA\Property(property="minimum_stock", type="string", example="1"),
     *                        @OA\Property(property="minimum_stock_alert", type="string", example="1"),
     *                        @OA\Property(property="percentage_discount", type="string", example="10.0"),
     *                        @OA\Property(property="all_categories",type="array",
     *                          @OA\Items(type="integer",example="2")
     *                        ),
     *                        @OA\Property(property="Variant",type="array",
     *                           @OA\Items(type="object",
     *                               @OA\Property(property="id",type="string",example="162")
     *                           )
     *                      )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean",example="false"),
     *             @OA\Property(property="status", type="integer",example="404"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string",example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean",example="false"),
     *             @OA\Property(property="status", type="integer",example="400"),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string",example="The :id parameter must be of integer type.")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $product = DB::connection('enjoy')->table('products as p')
                ->select('p.*')
                ->addSelect('b.name as brand_name')
                ->addSelect('c.name as category_name')
                ->selectSub(function ($query) {
                    $query->selectRaw('count(od.id)')
                        ->from('order_details as od')
                        ->where('od.payment_status', '=', 'paid')
                        ->whereRaw('od.product_id = p.id')
                        ->groupBy('od.product_id');
                }, 'quantity_sold')
                ->selectRaw('GROUP_CONCAT(ps.id) AS variants_id')
                ->selectRaw('GROUP_CONCAT(ps.variant ORDER BY ps.variant) AS variants')
                ->leftJoin('brands as b', 'b.id', '=', 'p.brand_id')
                ->leftJoin('categories as c', 'c.id', '=', 'p.category_id')
                ->leftJoin('product_stocks as ps', 'ps.product_id', '=', 'p.id')
                ->where('p.id', '=', $id)
                ->groupBy('p.id', 'p.name', 'b.name', 'c.name')
                ->first();
            if ($product !== null) {

                $categoryIdsQuery = DB::connection('enjoy')
                    ->select("WITH RECURSIVE CategoryHierarchy AS (
                    SELECT id, parent_id, name
                        FROM categories
                    WHERE id = :category_id
                    UNION ALL
                    SELECT c.id, c.parent_id, c.name
                        FROM categories c
                    JOIN CategoryHierarchy ch ON c.id = ch.parent_id
                    )
                    SELECT id FROM CategoryHierarchy;", ['category_id' => $product->category_id]
                    );

                $categoryIds = array_column($categoryIdsQuery, 'id');

                if ($product->discount_type === 'amount') {
                    $promotionalPrice = $product->unit_price - $product->discount;
                } else {
                    $discountedPrice = $product->unit_price * ($product->discount / 100);
                    $promotionalPrice = $product->unit_price - ceil($discountedPrice);
                }

                $photoIds = explode(',', $product->photos);
                $photos = DB::connection('enjoy')->table('uploads')->whereIn('id', $photoIds)->get();
                $product->photo_names = $photos->pluck('file_name')->toArray();

                $photosData = [];

                foreach ($product->photo_names as $photo) {
                    $photosInfo = [
                        'http' => $this->urlService->getHttpUrl() . 'public/' . $photo,
                        'https' => $this->urlService->getHttpsUrl() . 'public/' . $photo
                    ];
                    $photosData[] = $photosInfo;
                }

                $variants = explode(',', $product->variants);
                $variantData = [];

                foreach ($variants as $variant) {
                    list($cor, $tamanho) = explode('-', $variant);
                    $variantInfo = [
                        'tamanho' => [$tamanho],
                        'cor' => [$cor],
                    ];
                    $variantData[] = $variantInfo;
                }

                $ids = explode(',', $product->variants_id);
                $variationIdData = [];

                foreach ($ids as $variant_id) {
                    $variationIdInfo = [
                        'id' => $variant_id
                    ];
                    $variationIdData[] = $variationIdInfo;
                }

                $data['Product'] = [
                    'modified' => $product->updated_at,
                    'slug' => $product->slug,
                    'id' => $product->id,
                    'name' => $product->name,
                    'description' => $product->description,
                    'price' => number_format($product->unit_price, 2),
                    'cost_price' => '0.00',
                    'dollar_cost_price' => '0.00',
                    'promotional_price' => number_format($promotionalPrice, 2),
                    'start_promotion' => $product->discount_start_date ? date('Y-m-d', $product->discount_start_date) : '0000-00-00',
                    'end_promotion' => $product->discount_end_date ? date('Y-m-d', $product->discount_end_date) : '0000-00-00',
                    'brand' => $product->brand_id ? $product->brand_name : '',
                    'brand_id' => $product->brand_id ? $product->brand_id : '',
                    'model' => '',
                    'weight' => $product->weight,
                    'length' => '0',
                    'width' => '0',
                    'height' => '0',
                    'stock' => $product->current_stock,
                    'category_id' => $product->category_id,
                    'category_name' => $product->category_name,
                    'available' => $product->published === 1 ? '1' : '0',
                    'availability' => '',
                    'reference' => '',
                    'hot' => '0',
                    'release' => '0',
                    'additional_button' => '0',
                    'has_variation' => $product->variant_product ? '1' : '0',
                    'has_acceptance_terms' => '0',
                    'has_buy_together' => '0',
                    'additional_message' => '',
                    'warranty' => '',
                    'rating' => '0',
                    'count_rating' => $product->rating,
                    'quantity_sold' => $product->quantity_sold,
                    'ProductImage' => $photosData,
                    'image' => count($photosData) > 0 ? '1' : '0',
                    'url' => [
                        'http' => $this->urlService->getHttpUrl() . 'produto/' . $product->slug,
                        'https' => $this->urlService->getHttpsUrl() . 'produto/' . $product->slug,
                    ],
                    'created' => $product->created_at,
                    'Properties' => $variantData,
                    'minimum_stock' => '1',
                    'minimum_stock_alert' => '1',
                    'percentage_discount' => $product->discount_type !== 'amount' ? number_format($product->discount, 2) : '0.00',
                    'all_categories' => $categoryIds,
                    'Variant' => $variationIdData
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

    //TODO: Atualizar Dados do Produto
    public function update(Request $request, string $id)
    {
        //
    }


    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Delete a product by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the product to be deleted",
     *         required=true,
     *         @OA\Schema(type="integer", format="int64"),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=204),
     *             @OA\Property(property="message", type="string", example="Product deleted successfully"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No data found for the given ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID."),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="The :id parameter must be of integer type."),
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
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $product = DB::connection('enjoy')->table('products as p')
                ->where('p.id', '=', $id)
                ->first();

            if ($product) {
                $product_variation = DB::connection('enjoy')->table('product_stocks as ps')
                    ->where('ps.product_id', '=', $id)
                    ->get();

                if ($product_variation->all()) {
                    $product_variation = DB::connection('enjoy')->table('product_stocks as ps')
                        ->where('ps.product_id', '=', $id)
                        ->delete();
                }

                DB::connection('enjoy')->table('products')->where('id', '=', $id)->delete();

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'Product deleted successfully'
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
     *     path="/api/products/{id}/sold",
     *     operationId="getSoldProducts",
     *     tags={"Products"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Get sold products by ID",
     *     description="Get sold products details by providing product ID.",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Product ID",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(
     *                     property="paging",
     *                     type="object",
     *                     @OA\Property(
     *                         property="total",
     *                         type="integer"
     *                     ),
     *                     @OA\Property(
     *                         property="page",
     *                         type="integer"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="sort",
     *                     type="object",
     *                     @OA\Property(
     *                         property="id",
     *                         type="string"
     *                     )
     *                 ),
     *                 @OA\Property(
     *                     property="ProductsSolds",
     *                     type="object",
     *                     @OA\Property(
     *                         property="ProductsSold",
     *                         type="array",
     *                         @OA\Items(
     *                             type="object",
     *                             @OA\Property(
     *                                 property="product_id",
     *                                 type="integer"
     *                             ),
     *                             @OA\Property(
     *                                 property="order_id",
     *                                 type="integer"
     *                             ),
     *                             @OA\Property(
     *                                 property="name",
     *                                 type="string"
     *                             ),
     *                             @OA\Property(
     *                                 property="price",
     *                                 type="string"
     *                             ),
     *                             @OA\Property(
     *                                 property="quantity",
     *                                 type="integer"
     *                             ),
     *                             @OA\Property(
     *                                 property="variation_id",
     *                                 type="integer"
     *                             ),
     *                             @OA\Property(
     *                                 property="reference",
     *                                 type="string"
     *                             )
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="success",
     *                 type="boolean"
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 type="integer"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="string"
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string"
     *             )
     *         )
     *     )
     * )
     */
    public function sold(string $id): JsonResponse
    {
        if (!is_numeric(trim($id))) {
            return response()->json([
                'success' => false,
                'status' => ResponseAlias::HTTP_BAD_REQUEST,
                'data' => [],
                'message' => 'The :id parameter must be an integer.'
            ], ResponseAlias::HTTP_BAD_REQUEST);
        }

        $productsSoldQuery = DB::connection('enjoy')->table('products as p')
            ->select('p.id as product_id', 'o.id as order_id', 'p.name', 'od.price', 'od.quantity')
            ->selectRaw('(select ps.id from product_stocks ps where ps.variant = od.variation and ps.product_id = p.id) as variation_id')
            ->selectRaw('(select ps.sku from product_stocks ps where ps.variant = od.variation and ps.product_id = p.id) as reference')
            ->leftJoin('order_details as od', 'od.product_id', '=', 'p.id')
            ->leftJoin('orders as o', 'o.id', '=', 'od.order_id')
            ->leftJoin('combined_orders as co', 'co.id', '=', 'o.combined_order_id')
            ->where('p.id', '=', $id)
            ->orderBy('p.id', 'ASC')
            ->paginate(10);

        $data = [
            'paging' => [
                'total' => $productsSoldQuery->total(),
                'page' => $productsSoldQuery->currentPage(),
            ],
            'sort' => [
                'id' => 'asc'
            ],
            'ProductsSolds' => [
                'ProductsSold' => $productsSoldQuery->items()
            ]
        ];

        return response()->json([
            'success' => true,
            'status' => ResponseAlias::HTTP_OK,
            'data' => $data
        ], ResponseAlias::HTTP_OK);
    }
}
