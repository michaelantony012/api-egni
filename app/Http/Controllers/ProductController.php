<?php

namespace App\Http\Controllers;

use App\Http\Resources\d_ProductResource;
use App\Http\Resources\opt_ProductResource;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Product::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => ProductResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function optionIndex()
    {
        $data = Product::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => opt_ProductResource::collection($data),
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
        $create = Product::create($request->all());
        return response()->json([
            'status' => $create ? true : false,
            'message' => 'Berhasil'
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Product::findOrFail($id);
        return response()->json([
            'data' => new d_ProductResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Product $product)
    {
        $updated = DB::table('products')
            ->where('id', $request->id)
            ->update([
                'barcode' => $request->barcode,
                'product_code' => $request->product_code,
                'product_name' => $request->product_name,
                'category_id' => $request->category_id,
                'sub_category_id' => $request->sub_category_id,
                'product_price' => $request->product_price,
                'primary_stock' => $request->primary_stock,
                'qty_count' => $request->qty_count,
                'join_id' => $request->join_id,
            ]);

        return response()->json([
            'status' => $updated ? true : false,
            'message' => 'Berhasil'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Product::destroy($id);
        return response()->json([
            'status' => $delete ? true : false,
            'message' => 'Berhasil'
        ]);
    }
}
