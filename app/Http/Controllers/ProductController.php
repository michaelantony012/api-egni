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
    public function index(Request $request)
    {

        // $data = Product::paginate($request->row);
        $data = DB::table('products as a')
            ->leftjoin('categories as b', 'a.category_id', 'b.id')
            ->leftjoin('sub_categories as c', function ($join) {
                $join->on('a.sub_category_id', 'c.id')
                    ->on('b.id', 'c.category_id');
            })
            ->leftjoin(
                DB::raw('(SELECT SUM(a.trans_qty) AS qty_stock, a.trans_loc, a.id_product
                                        FROM inventory_journal a
                                        inner join products b on a.id_product=b.id
                                        WHERE a.trans_loc = "' . $request->id_lokasi . '" and b.primary_stock <> 0
                                        GROUP BY a.trans_loc, a.id_product
                                    ) as d'),
                function ($join) {
                    $join->on('a.id', 'd.id_product');
                }
            )
            ->leftjoin(
                DB::raw('(SELECT a.trans_cogs, a.id_product, a.trans_loc
                                    FROM inventory_journal a
                                    inner join products b on a.id_product=b.id
                                    WHERE a.trans_loc = "' . $request->id_lokasi . '" and b.primary_stock <> 0
                                    ORDER BY a.trans_date DESC
                                    LIMIT 1
                                    ) as e'),
                function ($join) {
                    $join->on('a.id', 'e.id_product');
                }
            )
            ->select('a.*', 'b.category_name', 'c.sub_category_name', DB::raw('ifnull(d.qty_stock,0) as qty_stock'), DB::raw('ifnull(e.trans_cogs,0) as trans_cogs'))
            ->where('primary_stock','=',$request->primary_stock)
            ->paginate($request->row);
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'first_page' => 1,
            'last_page' => ceil($data->total() / $data->perPage()),
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
        // $data = Product::where('product_code', "LIKE", '%' . $request->words . '%')
        $data = Product::where('barcode', "LIKE", '%' . $request->words . '%')
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
        if ($request->primary_stock == 0 && (!$request->join_id || $request->join_id == 0 || !$request->qty_count || $request->qty_count == 0)) {
            return response()->json([
                'status' => false,
                'message' => 'Non primary stock item!'
            ]);
        }
        $create = Product::create($request->all());
        return response()->json([
            'status' => $create ? true : false,
            'message' => 'Berhasil',
            'data' => $create
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
            // 'user_id' => $request->user_id, // asumsi ini ikut dilempar
            'location_id' => $request->location_id, // asumsi ini ikut dilempar

        ]);

        $items = $request->items;
        // $items = json_decode($request->items, true);
        for ($i = 0; $i < count($items); $i++) {
            $create_item = Product::create([
                'barcode' => $items[$i]['barcode'],
                'product_code' => $items[$i]['product_code'],
                'product_name' => $items[$i]['product_name'],
                'category_id' => $items[$i]['category_id'],
                'sub_category_id' => $items[$i]['sub_category_id'],
                'product_price' => $items[$i]['product_price'],
                'primary_stock' => $items[$i]['primary_stock'] ? 1 : 0,
                // 'qty_count' => $request->qty_count, // tidak perlu krn ini utk item non primary saja
                // 'join_id' => $request->join_id // tidak perlu krn ini utk item non primary saja
            ]);

            if ($items[$i]['qty'] > 0) {
                $add_beg_detail = BeginningStockDetail::create([
                    'id_header' => $add_beg_header->id,
                    'id_product' => $create_item->id,
                    'qty' => $items[$i]['qty'],
                    // 'keterangan' => $items[$i]['keterangan'],
                    // 'price' => $items[$i]['price'], // beg stock yg tanpa harga
                    // 'total_price' => $items[$i]['total_price'],
                ]);
            }

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
        $updbegflow1 = new DocFlowController();
        $content1 = new Request([
            'doctype_id' => 3,
            'doc_id' => $add_beg_header->id,
            'flow_prev' => 1,
            'flow_next' => 10
        ]);
        $updbegflow1->updateFlow($content1);

        $updbegflow2 = new DocFlowController();
        $content2 = new Request([
            'doctype_id' => 3,
            'doc_id' => $add_beg_header->id,
            'flow_prev' => 10,
            'flow_next' => 100
        ]);
        $updbegflow2->updateFlow($content2);

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


    public function stockMutationDetail(Request $request)
    {

        $sql = "


        SELECT trans_date, trans_qty, trans_in, trans_out, @Balance := @Balance + X.trans_qty AS Balance,
        id_product, product_code, product_name, trans_loc, loc_name
        FROM (
            SELECT 1 as num, ? AS trans_date, IFNULL(trans_qty,0) AS trans_qty, IFNULL(trans_in,0) AS trans_in, IFNULL(trans_out,0) AS trans_out,
            a.id AS id_product, '---' AS product_code, 'Beginning Balance' AS product_name, ?  as trans_loc, loc_name
            FROM products a
            LEFT JOIN (
                SELECT id_product, SUM(trans_qty) AS trans_qty, SUM(trans_in) AS trans_in, SUM(trans_out) AS trans_out, trans_loc
                FROM inventory_journal
                WHERE trans_loc = ?
                AND trans_date<?
                AND id_product=?
                GROUP BY id_product, trans_loc
            ) b ON a.id = b.id_product
            LEFT JOIN locations c ON 3 = c.id
            WHERE a.id=?
            UNION ALL
            SELECT 2 as num, a.trans_date, a.trans_qty, a.trans_in, a.trans_out, a.id_product, b.product_code, b.product_name, a.trans_loc, c.loc_name
            FROM inventory_journal a
            INNER JOIN products b ON a.id_product=b.id
            INNER JOIN locations c ON a.trans_loc=c.id
            WHERE trans_loc = ?
            AND trans_date BETWEEN ? AND ?
            AND id_product=?
        )X,
        (SELECT @Balance := 0) AS variableInit
        ORDER BY X.num, X.trans_date
        ";

        $results = DB::select($sql, [$request->start_date, $request->id_lokasi, $request->id_lokasi, $request->start_date, $request->id_product, $request->id_product, $request->id_lokasi, $request->start_date, $request->end_date, $request->id_product]);

        return response()->json([
            'status' => true,
            'data' => $results,
        ]);
    }

    public function stockCardAll(Request $request)
    {

        $sql = "

        select sum(beg_qty) as beg_qty, sum(in_qty) as in_qty, sum(out_qty) as out_qty, id_product,
        product_code, product_name, trans_loc, loc_name
        from (
            select sum(trans_qty) as beg_qty, 0 as in_qty, 0 as out_qty, id_product, trans_loc
            from inventory_journal
            where trans_loc = ?
            and trans_date<?
            group by id_product, trans_loc
            union all
            SELECT 0 as beg_qty, sum(trans_in) as in_qty, SUM(trans_out) AS out_qty, id_product, trans_loc
            FROM inventory_journal
            WHERE trans_loc = ?
            AND trans_date between ? and ?
            GROUP BY id_product, trans_loc
        )X
        INNER JOIN products Y ON X.id_product = Y.id
        INNER JOIN locations L ON X.trans_loc = L.id
        GROUP BY id_product, product_code, product_name, trans_loc, loc_name
        ORDER BY product_name";

        $results = DB::select($sql, [$request->id_lokasi, $request->start_date, $request->id_lokasi, $request->start_date, $request->end_date]);

        return response()->json([
            'status' => true,
            'data' => $results,
        ]);
    }


    public function stockCard(Request $request)
    {
        // if($this->__validate_token($request->header('token')) !== true) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'You are not authorized to send this request!'
        //     ]);
        // }

        $start_date = Carbon::create($request->start_date)->format('Y-m-d');
        $end_date = Carbon::create($request->end_date)->format('Y-m-d');

        $sql = "select
                    ax.*,
                    @Balance := @Balance + trans_in + trans_out + trans_beg AS balance
                from(
                    SELECT NULL AS id_product, NULL as doctype_id, NULL as doc_id, NULL AS trans_date,'Beginning Balance' AS trans_remark,'Beginning Balance' AS product_name, 0 AS trans_in, 0 AS trans_out, IFNULL(SUM(trans_qty),0) AS trans_beg
                    FROM inventory_journal
                    WHERE id_product=? AND trans_loc=? AND trans_date<?
                    UNION ALL
                    SELECT id_product, doctype_id, doc_id, trans_date, a.trans_remark, product_name, trans_in, trans_out, 0 as trans_beg
                    FROM inventory_journal AS a
                    INNER JOIN products AS b ON a.id_product=b.id
                    WHERE id_product=? AND trans_loc=? AND trans_date BETWEEN ? AND ?
                )ax ,  (SELECT @Balance := 0) AS variableInit";

        $results = DB::select($sql, [$request->id_product, $request->id_location, $start_date, $request->id_product, $request->id_location, $start_date, $end_date]);

        // if (collect($stock_beg)->isEmpty()) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => "Data tidak ditemukan"
        //     ], 404);
        // }
        return response()->json([
            'status' => true,
            'data' => $results,
        ]);
    }

    public function updateEnabledDisabled(Request $request){
        $barang = $request->update_product;

        foreach($barang as $barangs){
            $update = DB::table('products')->where('id',$barangs['id_product'])->update([
                'is_enabled' => $request->is_enabled
            ]);
        }

        return response()->json([
            'status' => $update ? true : false,
            'message' => 'Update Berhasil!'
        ]);

    }
}
