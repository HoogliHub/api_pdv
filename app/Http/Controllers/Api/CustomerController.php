<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
