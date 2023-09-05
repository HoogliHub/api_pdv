<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use OpenApi\Annotations as OA;
use Throwable;

class CustomerController extends Controller
{

    /**
     * @OA\Get(
     *     path="/api/enjoy/customers",
     *     operationId="getCustomers",
     *     summary="Get a list of customers",
     *     tags={"Customers"},
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
     *     path="/api/enjoy/customers/create",
     *     summary="Create a new user",
     *     description="Creates a new user and associates addresses with it.",
     *     tags={"Customers"},
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
                'password' => $request->input('password') ? Hash::make(substr(str_replace(['-', '.', '_', ' '], '', $request->input('cpf')), 0, 6)) : Hash::make($request->input('password')),
                'cpf' => str_replace(['-', '.', '_', ' '], '', $request->input('cpf')),
                'phone' => $request->input('phone'),
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
                    'postal_code' => str_replace(['-', '_', '.', ' '], '', $address['zip_code']),
                    'set_default' => !$address['default_address'] ? 0 : 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
            DB::commit();
            return response()->json([
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
     *     path="/api/enjoy/customers/show/{id}",
     *     summary="Retrieve customer details by ID",
     *     tags={"Customers"},
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
