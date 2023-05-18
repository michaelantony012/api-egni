<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use App\Http\Resources\CustomerResource;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Customer::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => CustomerResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $create = Customer::create($request->all());
        return response()->json([
            'status' => $create ? true : false,
            'message' => $create ? 'Berhasil' : 'Gagal'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Customer::findOrFail($id);
        return response()->json([
            'data' => new CustomerResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function edit(Customer $customer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Customer $customer)
    {
        $updated = DB::table('customers')
            ->where('id', $request->id)
            ->update([
                'customer_name' => $request->customer_name,
                'customer_contact' => $request->customer_contact,
                'customer_address' => $request->customer_address,
            ]);

        return response()->json([
            'status' => $updated ? true : false,
            'message' => $updated ? 'Berhasil' : 'Gagal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Customer  $customer
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Customer::destroy($id);
        return response()->json([
            'status' => $delete ? true : false,
            'message' => $delete ? 'Berhasil' : 'Gagal'
        ]);
    }
}
