<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\EnjoyUrlService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
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
     *     path="/api/enjoy/products",
     *     operationId="getProducts",
     *     tags={"Products"},
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

    //TODO: Cadastrar Produto
    public function store(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => 'required|string',
            'description' => 'string',
            'price' => 'required|',
            'start_promotion' => 'date',
            'end_promotion' => 'date',
            'brand' => 'string',
            'weight' => '',
            'stock' => '',
            'category_id' => 'required',
            'available' => '',
            'virtual_product' => '',
            'unit' => 'required'
        ]);

        dd($request);
    }

    /**
     * @OA\Get(
     *     path="/api/enjoy/products/show/{id}",
     *     summary="Get product details by ID",
     *     tags={"Products"},
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

    //TODO: Exclusão de Produtos
    public function destroy(string $id)
    {
        //
    }

    /**
     * @OA\Get(
     *     path="/enjoy/products/sold/{id}",
     *     operationId="getSoldProducts",
     *     tags={"Products"},
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
