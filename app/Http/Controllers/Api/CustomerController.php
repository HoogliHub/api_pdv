<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use OpenApi\Annotations as OA;
use Throwable;

class CustomerController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/customers",
     *     operationId="getCustomers",
     *     summary="Get a list of customers",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     description="Retrieve a list of customers with sorting, paging, and filtering options.",
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Sort orders by a specific field",
     *         @OA\Schema(type="string", enum={"id", "name", "email", "cpf", "phone", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sorting order (asc or desc)",
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
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
     *         description="Successful response with a list of customers",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="sort", type="object",
     *                     @OA\Property(property="field", type="string"),
     *                     @OA\Property(property="direction", type="string")
     *                 ),
     *                 @OA\Property(property="fieldsAvailableSortBy", type="array",
     *                     @OA\Items(type="string", enum={"id", "name", "email", "cpf", "phone", "created_at", "updated_at"})
     *                 ),
     *                 @OA\Property(property="Customers", type="array",
     *                     @OA\Items(type="object",
     *                         @OA\Property(property="Customer", type="object",
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="name", type="string"),
     *                             @OA\Property(property="cpf", type="string"),
     *                             @OA\Property(property="email", type="string"),
     *                             @OA\Property(property="phone", type="string"),
     *                             @OA\Property(property="country", type="string"),
     *                             @OA\Property(property="state", type="string"),
     *                             @OA\Property(property="city", type="string"),
     *                             @OA\Property(property="created", type="string"),
     *                             @OA\Property(property="modified", type="string"),
     *                             @OA\Property(property="CustomerAddress", type="object",
     *                                 @OA\Property(property="id", type="integer")
     *                             )
     *                         )
     *                     )
     *                 ),
     *                 @OA\Property(property="paging", type="object",
     *                     @OA\Property(property="total", type="integer"),
     *                     @OA\Property(property="page", type="integer"),
     *                     @OA\Property(property="limit", type="integer"),
     *                     @OA\Property(property="lastPage", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter type",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success",type="boolean",example="false"),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data",type="array",@OA\Items(type="string")),
     *             @OA\Property(property="message", type="string"),
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
                'name',
                'email',
                'cpf',
                'phone',
                'created_at',
                'updated_at',
            ]
        ];

        $customersQuery = DB::connection('enjoy')->table('users as u')
            ->select('u.*')
            ->addSelect('a.id as address_id')
            ->selectRaw('(select c.name from countries c where c.id = a.country_id) as country_name')
            ->selectRaw('(select c2.name from cities c2 where c2.id = a.city_id) as city_name')
            ->selectRaw('(select s.name from states s where s.country_id = a.country_id and s.id = a.state_id) as state_name')
            ->leftJoin('addresses as a', 'a.user_id', '=', 'u.id')
            ->where('u.user_type', '=', 'customer')
            ->orderBy('u.id' ?? 'u.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $customersQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $customersQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $customers = $customersQuery->get();
        } else {
            $customers = $customersQuery->paginate(10);
            $paging_data = [
                "total" => $customers->total(),
                "page" => $customers->currentPage(),
                "limit" => $customers->perPage(),
                "lastPage" => $customers->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        foreach ($customers as $customer) {
            $customer_data = [
                'Customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'cpf' => $customer->cpf,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'country' => $customer->country_name,
                    'state' => $customer->state_name,
                    'city' => $customer->city_name,
                    'created' => $customer->created_at,
                    'modified' => $customer->updated_at,
                    'CustomerAddress' => [
                        'id' => $customer->address_id
                    ]
                ]
            ];

            $data['Customers'][] = $customer_data;
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/customers/create",
     *     summary="Create a new user",
     *     description="Creates a new user and associates addresses with it.",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data and addresses",
     *         @OA\JsonContent(
     *             required={"name", "email", "cpf", "phone", "CustomerAddress"},
     *             @OA\Property(property="name", type="string", description="User name"),
     *             @OA\Property(property="email", type="string", format="email", description="User email"),
     *             @OA\Property(property="password", type="string", description="User password"),
     *             @OA\Property(property="cpf", type="string", description="User password. If not provided, it will be generated from the first six digits of the CPF."),
     *             @OA\Property(property="phone", type="string", description="User phone"),
     *             @OA\Property(
     *                 property="CustomerAddress",
     *                 type="array",
     *                 description="Customer address list",
     *                 @OA\Items(
     *                     @OA\Property(property="address", type="string", description="Address"),
     *                     @OA\Property(property="country", type="string", description="Address country"),
     *                     @OA\Property(property="state", type="string", description="Address state"),
     *                     @OA\Property(property="city", type="string", description="Address city"),
     *                     @OA\Property(property="zip_code", type="string", description="Address zip code"),
     *                     @OA\Property(property="default_address", type="boolean", description="Indicates whether it is the default address (true) or not (false).")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Usuário criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User Created Successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="message", type="string", example="Required fields are missing or incorrect."),
     *             @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Data conflict",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=409),
     *             @OA\Property(property="message", type="string", example="There is already a record with the given email.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'password' => 'string',
            'cpf' => 'required|string',
            'phone' => 'required|string',
            'CustomerAddress' => 'required|array',
            'CustomerAddress.*.address' => 'required|string',
            'CustomerAddress.*.country' => 'required|string',
            'CustomerAddress.*.state' => 'required|string',
            'CustomerAddress.*.city' => 'required|string',
            'CustomerAddress.*.zip_code' => 'required|string',
            'CustomerAddress.*.default_address' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Required fields are missing or incorrect.',
                'erros' => $validator->errors()
            ], 400);
        }

        $email_already_exists = DB::connection('enjoy')->table('users')->where('email', 'LIKE', '%' . $request->input('email') . '%')->first();
        $cpf_already_exists = DB::connection('enjoy')->table('users')->where('cpf', 'LIKE', '%' . $request->input('cpf') . '%')->orWhere('cpf', 'LIKE', '%' . str_replace(['.', '-', '_', ' '], '', $request->input('cpf')) . '%')->first();

        if ($email_already_exists !== null) {
            return response()->json([
                'success' => false,
                'status' => 409,
                'message' => 'There is already a record with the given email.',
            ], 409);
        }

        if ($cpf_already_exists !== null) {
            return response()->json([
                'success' => false,
                'status' => 409,
                'message' => 'There is already a record with the given cpf.',
            ], 409);
        }

        try {
            DB::beginTransaction();

            $userId = DB::connection('enjoy')->table('users')->insertGetId([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => $request->input('password') ? Hash::make(get_six_digits_cpf(get_normalized_string($request->input('cpf')))) : Hash::make($request->input('password')),
                'cpf' => get_normalized_string($request->input('cpf')),
                'phone' => get_normalized_string($request->input('phone')),
                'created_at' => now(),
                'updated_at' => now(),
                'user_type' => 'customer'
            ]);

            foreach ($request->input('CustomerAddress') as $address) {
                $country = DB::connection('enjoy')->table('countries')->select('id')->where('name', 'LIKE', '%' . ($address['country'] == 'Brasil' ? 'Brazil' : $address['country']) . '%')->first();
                $state = DB::connection('enjoy')->table('states')->select('id')->where('name', 'LIKE', '%' . $address['state'] . '%')->where('country_id', '=', $country->id)->first();
                $city = DB::connection('enjoy')->table('cities')->select('id')->where('name', 'LIKE', '%' . $address['city'] . '%')->where('state_id', '=', $state->id)->first();

                DB::connection('enjoy')->table('addresses')->insert([
                    'user_id' => $userId,
                    'address' => $address['address'],
                    'country_id' => $country->id,
                    'state_id' => $state->id,
                    'city_id' => $city->id,
                    'postal_code' => get_normalized_string($address['zip_code']),
                    'set_default' => !$address['default_address'] ? 0 : 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            DB::commit();
            return response()->json([
                'success' => true,
                'code' => 201,
                'status' => true,
                'message' => 'User Created Successfully'
            ], 200);
        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Internal server error',
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/customers/{id}",
     *     summary="Retrieve customer details by ID",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the customer to retrieve",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Customer details retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="Customer", type="object",
     *                     @OA\Property(property="id", type="integer"),
     *                     @OA\Property(property="name", type="string"),
     *                     @OA\Property(property="cpf", type="string"),
     *                     @OA\Property(property="phone", type="string"),
     *                     @OA\Property(property="email", type="string"),
     *                     @OA\Property(property="total_orders", type="integer"),
     *                     @OA\Property(property="last_purchase", type="string", format="date"),
     *                     @OA\Property(property="address", type="string"),
     *                     @OA\Property(property="zip_code", type="string"),
     *                     @OA\Property(property="country", type="string"),
     *                     @OA\Property(property="state", type="string"),
     *                     @OA\Property(property="city", type="string"),
     *                     @OA\Property(property="created", type="string", format="date-time"),
     *                     @OA\Property(property="modified", type="string", format="date-time"),
     *                     @OA\Property(property="CustomerAddress", type="object",
     *                         @OA\Property(property="id", type="integer")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Customer not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Não há dados para o ID fornecido.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid parameter type",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="O parâmetro :id deve ser do tipo inteiro.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $customer = DB::connection('enjoy')->table('users as u')
                ->select('u.*')
                ->addSelect('a.id as address_id', 'a.address', 'a.postal_code as zip_code')
                ->selectRaw('(select c.name from countries c where c.id = a.country_id) as country_name')
                ->selectRaw('(select c2.name from cities c2 where c2.id = a.city_id) as city_name')
                ->selectRaw('(select s.name from states s where s.country_id = a.country_id and s.id = a.state_id) as state_name')
                ->selectRaw("(select count(o.id) from orders o where o.payment_status = 'paid' and o.user_id = " . $id . ") as total_orders")
                ->selectRaw("(select o.date from orders o where o.payment_status = 'paid' and o.user_id = " . $id . " order by o.created_at desc) as last_purchase")
                ->leftJoin('addresses as a', 'a.user_id', '=', 'u.id')
                ->where('u.user_type', '=', 'customer')
                ->where('u.id', '=', $id)
                ->first();

            if ($customer) {
                $data['Customer'] = [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'cpf' => $customer->cpf,
                    'phone' => $customer->phone,
                    'email' => $customer->email,
                    'total_orders' => $customer->total_orders,
                    'last_purchase' => $customer->last_purchase ? date('Y-m-d', $customer->last_purchase) : null,
                    'address' => $customer->address,
                    'zip_code' => $customer->zip_code,
                    'country' => $customer->country_name,
                    'state' => $customer->state_name,
                    'city' => $customer->city_name,
                    'created' => $customer->created_at,
                    'modified' => $customer->updated_at,
                    'CustomerAddress' => [
                        'id' => $customer->address_id
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
     *     path="/api/customers/{id}",
     *     summary="Update a user",
     *     description="Updates a user and associated addresses.",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the user",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         description="User data and addresses",
     *         @OA\JsonContent(
     *             required={"name", "email", "cpf", "phone", "CustomerAddress"},
     *             @OA\Property(property="name", type="string", description="User name"),
     *             @OA\Property(property="email", type="string", format="email", description="User email"),
     *             @OA\Property(property="password", type="string", description="User password"),
     *             @OA\Property(property="cpf", type="string", description="User CPF"),
     *             @OA\Property(property="phone", type="string", description="User phone"),
     *             @OA\Property(
     *                 property="CustomerAddress",
     *                 type="array",
     *                 description="Customer address list",
     *                 @OA\Items(
     *                     @OA\Property(property="id", type="integer", description="Address ID"),
     *                     @OA\Property(property="address", type="string", description="Address"),
     *                     @OA\Property(property="country", type="string", description="Address country"),
     *                     @OA\Property(property="state", type="string", description="Address state"),
     *                     @OA\Property(property="city", type="string", description="Address city"),
     *                     @OA\Property(property="zip_code", type="string", description="Address zip code"),
     *                     @OA\Property(property="default_address", type="boolean", description="Indicates whether it is the default address (true) or not (false)."),
     *                     @OA\Property(property="latitude", type="number", format="float", description="Latitude"),
     *                     @OA\Property(property="longitude", type="number", format="float", description="Longitude")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(property="message", type="string", example="User updated successfully")
     *         )
     *     ),
     *     @OA\Response(
     *          response=400,
     *          description="Erro de validação",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=false),
     *              @OA\Property(property="status", type="integer", example=400),
     *              @OA\Property(property="message", type="string", example="Required fields are missing or incorrect."),
     *              @OA\Property(property="errors", type="object", example={"name": {"The name field is required."}})
     *          )
     *      ),
     *     @OA\Response(
     *         response=404,
     *         description="There is no data for the given ID",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'string',
            'email' => 'email',
            'password' => 'string',
            'cpf' => 'string|cpf',
            'phone' => 'string',
            'CustomerAddress' => 'array',
            'CustomerAddress.*.id' => ['integer', Rule::requiredIf($request->has('CustomerAddress'))],
            'CustomerAddress.*.address' => 'string',
            'CustomerAddress.*.country' => 'string',
            'CustomerAddress.*.state' => 'string',
            'CustomerAddress.*.city' => 'string',
            'CustomerAddress.*.zip_code' => 'string',
            'CustomerAddress.*.default_address' => 'boolean',
            'CustomerAddress.*.latitude' => 'latitude',
            'CustomerAddress.*.longitude' => 'longitude',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'status' => 400,
                'message' => 'Required fields are missing or incorrect.',
                'erros' => $validator->errors()
            ], 400);
        }

        try {
            DB::beginTransaction();

            if (is_numeric(trim($id))) {
                $customer = DB::connection('enjoy')->table('users')->where('id', '=', $id);
                if ($customer) {
                    $update_customer_data = [
                        'updated_at' => now()
                    ];

                    if ($request->has('name')) {
                        $update_customer_data['name'] = $request->input('name');
                    }
                    if ($request->has('email')) {
                        $update_customer_data['email'] = $request->input('email');
                    }
                    if ($request->has('password')) {
                        $update_customer_data['password'] = Hash::make($request->input('password'));
                    }
                    if ($request->has('cpf')) {
                        $update_customer_data['cpf'] = get_normalized_string($request->input('cpf'));
                    }
                    if ($request->has('phone')) {
                        $update_customer_data['phone'] = get_normalized_string($request->input('phone'));
                    }

                    $customer->update($update_customer_data);

                    if ($request->has('CustomerAddress')) {

                        $update_address_data = [
                            'updated_at' => now()
                        ];

                        foreach ($request->input('CustomerAddress') as $address) {
                            $customer_address = DB::connection('enjoy')->table('addresses')
                                ->where('user_id', $customer->pluck('id')->first())
                                ->where('id', $address['id']);

                            if ($address['country']) {
                                $country = DB::connection('enjoy')->table('countries')->select('id')->where('name', 'LIKE', '%' . ($address['country'] == 'Brasil' ? 'Brazil' : $address['country']) . '%')->first();
                                $update_address_data['country_id'] = $country->id;
                            }

                            if ($address['state']) {
                                $state = DB::connection('enjoy')->table('states')->select('id')->where('name', 'LIKE', '%' . $address['state'] . '%')->where('country_id', '=', $country->id)->first();
                                $update_address_data['state_id'] = $state->id;
                            }

                            if ($address['city']) {
                                $city = DB::connection('enjoy')->table('cities')->select('id')->where('name', 'LIKE', '%' . $address['city'] . '%')->where('state_id', '=', $state->id)->first();
                                $update_address_data['city_id'] = $city->id;
                            }

                            if ($address['address']) {
                                $update_address_data['address'] = $address['address'];
                            }
                            if ($address['default_address']) {
                                $update_address_data['set_default'] = !$address['default_address'] ? 0 : 1;
                            }
                            if ($address['latitude']) {
                                $update_address_data['latitude'] = $address['latitude'];
                            }
                            if ($address['longitude']) {
                                $update_address_data['longitude'] = $address['longitude'];
                            }
                            if ($address['zip_code']) {
                                $update_address_data['postal_code'] = get_normalized_string($address['zip_code']);
                            }

                            $customer_address->update($update_address_data);
                        }
                    }
                    DB::commit();
                    return response()->json([
                        'success' => true,
                        'code' => 204,
                        'status' => true,
                        'message' => 'User updated successfully'
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

        } catch (Throwable $th) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'status' => 500,
                'message' => 'Internal server error',
            ], 500);
        }

    }

    /**
     * @OA\Delete(
     *     path="/api/customers/{id}",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     summary="Delete a customer and associated addresses by ID.",
     *     operationId="deleteCustomer",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the customer to delete.",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="No Content",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="status", type="integer", example=204),
     *                 @OA\Property(property="message", type="string", example="User deleted successfully")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Not Found",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="status", type="integer", example=404),
     *                 @OA\Property(property="data", type="object"),
     *                 @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 @OA\Property(property="success", type="boolean", example=false),
     *                 @OA\Property(property="status", type="integer", example=400),
     *                 @OA\Property(property="data", type="object"),
     *                 @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *             )
     *         )
     *     )
     * )
     */
    public function destroy(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {

            $customer = DB::connection('enjoy')->table('users as u')
                ->where('u.user_type', '=', 'customer')
                ->where('u.id', '=', $id)
                ->first();
            if ($customer) {
                DB::connection('enjoy')->table('addresses as a')
                    ->where('a.user_id', '=', $customer->id)
                    ->delete();

                DB::connection('enjoy')->table('users')->delete($customer->id);

                return response()->json([
                    'success' => true,
                    'status' => 204,
                    'message' => 'User deleted successfully'
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
     *     path="/api/customers/addresses",
     *     summary="Get a list of addresses",
     *     description="Retrieve a list of addresses with optional sorting and pagination.",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="sort",
     *         in="query",
     *         description="Field to sort by",
     *         required=false,
     *         @OA\Schema(type="string", enum={"id", "user_id", "address", "postal_code", "created_at", "updated_at"})
     *     ),
     *     @OA\Parameter(
     *         name="order",
     *         in="query",
     *         description="Sort order",
     *         required=false,
     *         @OA\Schema(type="string", enum={"asc", "desc"})
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Number of records per page",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=1)
     *     ),
     *     @OA\Parameter(
     *         name="offset",
     *         in="query",
     *         description="Number of records to skip for pagination",
     *         required=false,
     *         @OA\Schema(type="integer", minimum=0)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of addresses",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="sort", type="array", @OA\Items(type="string", enum={"id", "user_id", "address", "postal_code", "created_at", "updated_at"})),
     *                     @OA\Property(property="fieldsAvailableSortBy", type="array", @OA\Items(type="string", enum={"id", "user_id", "address", "postal_code", "created_at", "updated_at"})),
     *                     @OA\Property(property="paging", type="object", @OA\Property(property="total", type="integer"), @OA\Property(property="page", type="integer"), @OA\Property(property="limit", type="integer"), @OA\Property(property="lastPage", type="integer")),
     *                     @OA\Property(property="CustomerAddresses", type="array", @OA\Items(
     *                         @OA\Property(property="id", type="integer"),
     *                         @OA\Property(property="user_id", type="integer"),
     *                         @OA\Property(property="address", type="string"),
     *                         @OA\Property(property="country", type="string"),
     *                         @OA\Property(property="state", type="string"),
     *                         @OA\Property(property="city", type="string"),
     *                         @OA\Property(property="longitude", type="number", format="float"),
     *                         @OA\Property(property="latitude", type="number", format="float"),
     *                         @OA\Property(property="zip_code", type="string"),
     *                         @OA\Property(property="default_address", type="boolean"),
     *                         @OA\Property(property="created_at", type="string", format="date-time"),
     *                         @OA\Property(property="updated_at", type="string", format="date-time")
     *                     ))
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=500),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
    public function address_index(Request $request): JsonResponse
    {
        $data = [
            'sort' => [
                'field' => $request->query->get('sort') ?? 'id',
                'direction' => $request->query->get('order') ?? 'asc'
            ],
            'fieldsAvailableSortBy' => [
                'id',
                'user_id',
                'address',
                'postal_code',
                'created_at',
                'updated_at',
            ]
        ];

        $addressQuery = DB::connection('enjoy')->table('addresses as a')
            ->select('a.*')
            ->orderBy('a.id' ?? 'a.' . $request->query->get('sort'), $request->query->get('order') ?? 'asc');

        if ($request->query->get('limit')) {
            $addressQuery->limit($request->query->get('limit'));
        }

        if ($request->query->get('offset')) {
            $addressQuery->offset($request->query->get('offset'));
        }

        if ($request->query->get('limit') || $request->query->get('offset')) {
            $addresses = $addressQuery->get();
        } else {
            $addresses = $addressQuery->paginate(10);
            $paging_data = [
                "total" => $addresses->total(),
                "page" => $addresses->currentPage(),
                "limit" => $addresses->perPage(),
                "lastPage" => $addresses->lastPage()
            ];
            $data['paging'] = $paging_data;
        }

        foreach ($addresses as $address) {
            $country = DB::connection('enjoy')->table('countries')->select('name')->where('id', $address->country_id)->first();
            $state = DB::connection('enjoy')->table('states')->select('name')->where('id', $address->state_id)->first();
            $city = DB::connection('enjoy')->table('cities')->select('name')->where('id', $address->city_id)->first();
            $address_data = [
                'CustomerAddress' => [
                    'id' => $address->id,
                    'user_id' => $address->user_id,
                    'address' => $address->address,
                    'country' => $country?->name == 'Brazil' ? 'Brasil' : $country->name,
                    'state' => $state?->name,
                    'city' => $city?->name,
                    'longitude' => $address->longitude,
                    'latitude' => $address->latitude,
                    'zip_code' => $address->postal_code,
                    'default_address' => !($address->set_default == 0),
                    'created_at' => $address->created_at,
                    'updated_at' => $address->updated_at
                ]
            ];

            $data['CustomerAddresses'][] = $address_data;
        }

        return response()->json([
            'success' => true,
            'status' => 200,
            'data' => $data
        ], 200);
    }

    /**
     * @OA\Get(
     *     path="/api/customers/addresses/{id}",
     *     summary="Get a specific address",
     *     description="Retrieve a specific address by its ID.",
     *     tags={"Customers"},
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the address",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Address information",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=200),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="CustomerAddress", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer"),
     *                             @OA\Property(property="user_id", type="integer"),
     *                             @OA\Property(property="address", type="string"),
     *                             @OA\Property(property="country", type="string"),
     *                             @OA\Property(property="state", type="string"),
     *                             @OA\Property(property="city", type="string"),
     *                             @OA\Property(property="longitude", type="number", format="float"),
     *                             @OA\Property(property="latitude", type="number", format="float"),
     *                             @OA\Property(property="zip_code", type="string"),
     *                             @OA\Property(property="default_address", type="boolean"),
     *                             @OA\Property(property="created_at", type="string", format="date-time"),
     *                             @OA\Property(property="updated_at", type="string", format="date-time")
     *                         )
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Address not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="status", type="integer", example=404),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="There is no data for the given ID.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid ID format",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="status", type="integer", example=400),
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="The :id parameter must be of integer type.")
     *         )
     *     )
     * )
     */
    public function address_show(string $id): JsonResponse
    {
        if (is_numeric(trim($id))) {
            $addressQuery = DB::connection('enjoy')->table('addresses as a')
                ->select('a.*')
                ->where('a.id', '=', $id)
                ->first();
            if ($addressQuery) {
                $country = DB::connection('enjoy')->table('countries')->select('name')->where('id', $addressQuery->country_id)->first();
                $state = DB::connection('enjoy')->table('states')->select('name')->where('id', $addressQuery->state_id)->first();
                $city = DB::connection('enjoy')->table('cities')->select('name')->where('id', $addressQuery->city_id)->first();
                $data['CustomerAddress'] = [
                    'id' => $addressQuery->id,
                    'user_id' => $addressQuery->user_id,
                    'address' => $addressQuery->address,
                    'country' => $country?->name == 'Brazil' ? 'Brasil' : $country->name,
                    'state' => $state?->name,
                    'city' => $city?->name,
                    'longitude' => $addressQuery->longitude,
                    'latitude' => $addressQuery->latitude,
                    'zip_code' => $addressQuery->postal_code,
                    'default_address' => !($addressQuery->set_default == 0),
                    'created_at' => $addressQuery->created_at,
                    'updated_at' => $addressQuery->updated_at
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
}
