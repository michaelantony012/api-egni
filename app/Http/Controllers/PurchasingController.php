<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchasingResource;
use App\Models\Purchasing;
use Illuminate\Http\Request;

class PurchasingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Purchasing::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => PurchasingResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function show(Purchasing $purchasing)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchasing $purchasing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchasing $purchasing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchasing $purchasing)
    {
        //
    }
}
