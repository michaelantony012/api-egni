<?php

namespace App\Http\Controllers;

use App\Models\DocType;
use App\Models\Product;
use App\Models\DocFlowLogic;
use Illuminate\Http\Request;
use App\Models\BeginningStock;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\BeginningStockDetail;
use App\Http\Resources\ProductResource;
use App\Http\Resources\d_ProductResource;
use App\Http\Controllers\DocFlowController;
use App\Http\Resources\opt_ProductResource;
use App\Http\Controllers\BeginningStockController;

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
    public function search(Request $request)
    {
        // $data = DB::table('products')->where('product_code', "LIKE", '%' . $request->words . '%')->get();
        $data = Product::where('product_code', "LIKE", '%' . $request->words . '%')
        ->where('primary_stock', true)
        ->get();
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
        if($request->primary_stock==0 && (!$request->join_id || $request->join_id==0 || !$request->qty_count || $request->qty_count==0))
        {
            return response()->json([
                'status' => false,
                'message' => 'Non primary stock item!'
            ]);
        }
        $create = Product::create($request->all());
        return response()->json([
            'status' => $create ? true : false,
            'message' => 'Berhasil'
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function massCreate(Request $request)
    {
        DB::beginTransaction();

        $add_beg_header = BeginningStock::create([
            'date_header' => Carbon::now()->format('Y-m-d'),
            'no_header' => "", //dibuat otomatis pada saat posting
            'user_id' => $request->user_id, // asumsi ini ikut dilempar
            'location_id' => $request->location_id, // asumsi ini ikut dilempar

        ]);

        // $items = $request->items;
        $items = json_decode($request->items, true);
        for ($i = 0; $i < count($items); $i++) {
            $create_item = Product::create([
                'barcode' => $items[$i]['barcode'],
                'product_code' => $items[$i]['product_code'],
                'product_name' => $items[$i]['product_name'],
                'category_id' => $items[$i]['category_id'],
                'sub_category_id' => $items[$i]['sub_category_id'],
                'product_price' => $items[$i]['product_price'],
                'primary_stock' => $items[$i]['primary_stock'],
                // 'qty_count' => $request->qty_count, // tidak perlu krn ini utk item non primary saja
                // 'join_id' => $request->join_id // tidak perlu krn ini utk item non primary saja
            ]);

            $add_beg_detail = BeginningStockDetail::create([
                'id_header' => $add_beg_header->id,
                'id_product' => $create_item->id,
                'qty' => $items[$i]['qty'],
                // 'keterangan' => $items[$i]['keterangan'],
                // 'price' => $items[$i]['price'], // beg stock yg tanpa harga
                // 'total_price' => $items[$i]['total_price'],
            ]);

            if (!$add_beg_detail || !$add_beg_header || !$create_item) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menambahkan data!'
                ], 422);
            }
        }
   
        DB::commit();

        // posting beginning stock
        $updbegflow = new DocFlowController();
        $content = new Request([
            'doctype_id' => 3,
            'doc_id' => $add_beg_header->id,
            'flow_prev' => 1,
            'flow_next' => 10
        ]);
        $updbegflow->updateFlow($content);
        // $updbegflow = new ProductController();
        // $updbegflow->updateFlow(3,$add_beg_header->id,1,10);

        return response()->json([
            'status' => true,
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
