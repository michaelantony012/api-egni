<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sales;
use App\Models\DocFlow;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Location;
use App\Models\SalesDetail;
use App\Models\SalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\SalesResource;
use App\Http\Resources\d_SalesResource;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // $data = Sales::Join('document_flow as b', function ($join) {
        //     $join->on('sales_invoice_h.doctype_id', 'b.doctype_id')
        //         ->on('sales_invoice_h.flow_seq', 'b.doc_flow');
        // })
        //     ->join('locations as c', 'sales_invoice_h.location_id', 'c.id')
        //     ->join('customers as d', 'sales_invoice_h.customer_id', 'd.id')
        //     ->select('sales_invoice_h.*', 'b.flow_desc', 'c.loc_name', 'd.customer_name')
        //     ->paginate($request->row);
        $data = Sales::where('no_header', 'LIKE', '%' . $request->no_header . '%')->paginate($request->row);
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            // 'first_page' => 1,
            'last_page' => ceil($data->total() / $data->perPage()),
            'data' => SalesResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function detailIndex($id)
    {
        # code...
    }

    public function docflow(Request $request)
    {
        $docflow = DocFlow::where('doctype_id', '=', $request->doctype_id)
            ->where('flow_prev', '=', $request->flow_seq)
            ->where('flow_next', '=', $request->flow_next)
            ->get();

        if ($docflow) {
            DB::select($docflow->query_check);
            DB::select($docflow->query_update);

            return response()->json([
                'status' => true,
                'message' => $docflow->flow_desc . ' Success'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Doc Flow not found'
            ]);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        $add_sales_header = Sales::create([
            'date_header' => Carbon::now()->format('Y-m-d'), //$request->date_header,
            'no_header' => "", //dibuat otomatis pada saat posting
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'customer_id' => $request->customer_id,
            //'subtotal' => $request->subtotal, // dicalculate dibawah
            'subtotal' => 0,
            'disc_value' => $request->disc_value,
            'disc_percent' => $request->disc_percent,
            // 'disc_percentvalue' => $request->disc_percentvalue,
            'disc_percentvalue' => 0,
            'extra_charge' => $request->extra_charge,
            // 'dpp' => $request->dpp, // dicalculate dibawah
            // 'vat_type' => $request->vat_type, // ppn belum kepake
            // 'vat_percent' => $request->vat_percent,
            // 'vat_value' => $request->vat_value,
            // 'grandtotal' => $request->grandtotal,
            'dpp' => 0,
            'vat_type' => 0,
            'vat_percent' => 0,
            'vat_value' => 0,
            'grandtotal' => 0,

        ]);
        $detail = $request->detail; // decode ke array dulu
        // $detail = json_decode($request->detail, true); // decode ke array dulu
        for ($i = 0; $i < count($detail); $i++) {
            $add_sales_detail = SalesDetail::create([
                'id_header' => $add_sales_header->id,
                'id_product' => $detail[$i]['id_product'],
                'qty' => $detail[$i]['qty'],
                // 'keterangan' => $detail[$i]['keterangan'],
                // 'margin' => $detail[$i]['margin'],
                'price' => $detail[$i]['product']['product_price'],
                'disc_value' => $detail[$i]['disc_value'],
                'total_price' => ($detail[$i]['qty'] * $detail[$i]['product']['product_price']) - $detail[$i]['disc_value'],
                'vat_value' => 0,
                'disc_percent' => 0,
                'vat_percent' => 0,
            ]);

            if (!$add_sales_detail || !$add_sales_header) {
                DB::rollBack();
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal menambahkan data'
                ], 422);
            }
        }
        // DB::rollBack(); // testing

        DB::commit();

        // sales invoice calculate
        DB::select('call SalesInvoice_CalculateTotal(?)', array($add_sales_header->id));

        // check Stock Lack kasir --> start
        $location_name = Location::where('id', $add_sales_header->location_id)->first();

        $stock_lack = DB::select("
        SELECT CONCAT(b.`product_name`,' Stock Kurang di lokasi ".$location_name->loc_name."','!') as description
        FROM  sales_invoice_d a
        INNER JOIN products b ON a.id_product=b.id
        LEFT JOIN (
            SELECT IFNULL(SUM(trans_qty),0) AS qty_stock, id_product 
            FROM inventory_journal a
            WHERE trans_date<= ".$add_sales_header->date_header." AND trans_loc= ".$add_sales_header->location_id." AND id_product=a.`id_product`
            GROUP BY id_product
        )c ON a.id_product=c.id_product
        WHERE id_header=".$add_sales_header->id." AND a.qty>IFNULL(c.qty_stock,0);
        ");

        // dd($stock_lack[0]->description);
        if($stock_lack)
        {
            return response()->json([
                'status' => false,
                'message' => $stock_lack[0]->description,
                'is_saved' => $request->is_posting,
                'id_header' => $add_sales_header->id, // id header document,
                'created_at' => $add_sales_header->created_at // id header document
            ]);
        }
        // check Stock Lack kasir --> end

        // update doc flow
        if ($request->is_posting) {
            // recording
            $updbegflow1 = new DocFlowController();
            $content1 = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $add_sales_header->id,
                'flow_prev' => 1,
                'flow_next' => 10
            ]);
            $updbegflow1->updateFlow($content1);

            // posting
            $content1 = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $add_sales_header->id,
                'flow_prev' => 10,
                'flow_next' => 100
            ]);
            $updbegflow1->updateFlow($content1);
        } else if (!$request->is_posting) {
            // recording
            $updbegflow1 = new DocFlowController();
            $content1 = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $add_sales_header->id,
                'flow_prev' => 1,
                'flow_next' => 10
            ]);
            $updbegflow1->updateFlow($content1);
        }


        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'is_saved' => $request->is_posting,
            'id_header' => $add_sales_header->id, // id header document,
            'created_at' => $add_sales_header->created_at // id header document
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
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    /*public function show($id)
    {
        $data = Sales::findOrFail($id);

        return response()->json([
            'data' => new d_SalesResource($data),
            // 'data' => $data,
            'message' => 'Data berhasil di dapat'
        ]);
    }*/

    public function show($id)
    {
        $result = [];
        $header = DB::select( DB::raw("
            SELECT a.id, no_header, date_header, flow_seq, a.doctype_id, b.name AS user,
            a.location_id, c.loc_name, a.customer_id, d.customer_name, e.flow_desc,
            is_printed, subtotal, disc_value, disc_percent, disc_percentvalue, extra_charge, dpp, vat_type, vat_percent,
            vat_value, grandtotal
            FROM sales_invoice_h AS a
            INNER JOIN users b ON a.user_id=b.id
            INNER JOIN locations c ON a.location_id=c.id
            INNER JOIN customers d ON a.customer_id=d.id
            INNER JOIN document_flow e ON a.doctype_id=e.doctype_id and a.flow_seq=e.doc_flow

            WHERE a.id=?
        "), [$id] );

        $location = Location::find($header[0]->location_id);
        $location = json_decode($location, true);
        // dd($location);  
        $customer = Customer::find($header[0]->customer_id);
        $customer = json_decode($customer, true);
        // dd($customer);  

        $result = (array)$header[0];
        $result['location'] = $location;
        $result['customer'] = $customer;

        $detail = SalesDetail::where('id_header', $id)->get();

        // dd(json_decode($detail, true));
        foreach(json_decode($detail, true) as $det)
        {
            // $result['detail'][] = $det;
            $product = Product::find($det['id_product']);
            $return_sum = DB::select( DB::raw("
                select IFNULL(sum(qty),0) as return_qty
                from sales_invoice_r
                where id_header=? and id_product=?
                group by id_product
            "), [$id, $det['id_product']] );
            $det['return_qty'] = $return_sum[0]->return_qty;
            $det['product'] = $product;
            $result['detail'][] = $det;
        }

        $return = SalesReturn::where('id_header', $id)->get();

        // dd(json_decode($detail, true));
        foreach(json_decode($return, true) as $ret)
        {
            // $result['detail'][] = $det;
            $product = Product::find($ret['id_product']);
            $ret['product'] = $product;
            $result['return'][] = $ret;
        }

        // dd($detail[1]->id_product);
        // for($x=0;$x<count($detail);$x++)
        // {
        //     $result['detail'][] = (array)$detail[$x];
        // }
        // dd($result);

        return response()->json([
            'data' => $result,
            'message' => 'Data berhasil di dapat'
        ]);
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function edit(Sales $sales)
    {
        //
    }

    public function crud_return(Request $request)
    {
        DB::beginTransaction();
        // status flow harus direvise dulu 100 -> 110 sebelum jalankan return
        $header = Sales::where('id', '=', $request->id)->where('flow_seq', '=', 110)->where('no_header', '!=', '')->first();
        if(collect($header)->isNotEmpty())
        {
            // $update_returns = json_decode($request->update_return, true);
            $update_returns = $request->update_return;
            if ($update_returns) {
                
                for ($i = 0; $i < count($update_returns); $i++) {
                    
                    $update_return = SalesReturn::where('id', $update_returns[$i]['id'])->first();
                    if (collect($update_return)->isNotEmpty()) {
                        $update_return->id_product = $update_returns[$i]['id_product'];
                        $update_return->qty = $update_returns[$i]['qty'];
                        $update_return->price = $update_returns[$i]['price'];
                        $update_return->total_price = $update_returns[$i]['qty']*$update_returns[$i]['price'];
                        $update_return->save();
                    }

                    if (!$update_return) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Update data return'
                        ], 422);
                    }
                }
            }
            
            // $delete_returns = json_decode($request->delete_return, true);
            $delete_returns = $request->delete_return;
            if ($delete_returns) {
                for ($i = 0; $i < count($delete_returns); $i++) {
                    $delete_return = SalesReturn::where('id', $delete_returns[$i]['id'])->delete();

                    if (!$delete_return) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Delete data return'
                        ], 422);
                    }
                }
            }

            // $create_returns = json_decode($request->create_return, true);
            $create_returns = $request->create_return;
            if ($create_returns) {
                for ($i = 0; $i < count($create_returns); $i++) {
                    $create_return = SalesReturn::create([
                        'id_header' => $request->id,
                        'id_product' => $create_returns[$i]['id_product'],
                        'qty' => $create_returns[$i]['qty'],
                        'price' => $create_returns[$i]['price'],
                        'total_price' => $create_returns[$i]['qty']*$create_returns[$i]['price'],
                    ]);

                    if (!$create_return) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Create data return'
                        ], 422);
                    }
                }
            }

            DB::commit();

            // sales invoice calculate with return
            DB::select('call SalesInvoice_CalculateTotal(?)', array($header->id));

            // return pengecekan qty tdk boleh minus
            $check_return_minus = DB::select( DB::raw("
            SELECT id_product, sales_qty, return_qty, h.product_code, h.product_name
            FROM (
            
                SELECT IFNULL(a.id_product, b.id_product) AS id_product, IFNULL(a.sales_qty,0) AS sales_qty, IFNULL(b.return_qty,0) AS return_qty
                FROM (
                SELECT id_product, SUM(qty) AS sales_qty
                FROM sales_invoice_d WHERE id_header=?
                GROUP BY id_product
                )a
                RIGHT JOIN (
                SELECT id_product, SUM(qty) AS return_qty
                FROM sales_invoice_r WHERE id_header=?
                GROUP BY id_product
                )b ON a.id_product=b.id_product
                UNION 
                SELECT IFNULL(a.id_product, b.id_product) AS id_product, IFNULL(a.sales_qty,0) AS sales_qty, IFNULL(b.return_qty,0) AS return_qty
                FROM (
                SELECT id_product, SUM(qty) AS sales_qty
                FROM sales_invoice_d WHERE id_header=?
                GROUP BY id_product
                )a
                LEFT JOIN (
                SELECT id_product, SUM(qty) AS return_qty
                FROM sales_invoice_r WHERE id_header=?
                GROUP BY id_product
                )b ON a.id_product=b.id_product
            )g
            LEFT JOIN products h on g.id_product=h.id
            WHERE sales_qty-return_qty<0
            "), [$request->id, $request->id, $request->id, $request->id] );

            // dd($check_return_minus);
            // dd(empty($check_return_minus));
            if(!empty($check_return_minus))
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Item '.$check_return_minus[0]->product_code.'::'.$check_return_minus[0]->product_name.' return '.$check_return_minus[0]->return_qty.' melebihi qty jual '.$check_return_minus[0]->sales_qty.'!'
                ]);
            }

            // auto posting 120
            $postflow = new DocFlowController();
            $content = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $request->id,
                'flow_prev' => 110,
                'flow_next' => 120
            ]);
            $postflow->updateFlow($content);

            return response()->json([
                'status' => true,
                'message' => 'Berhasil update/create/delete dan selesaikan return'
            ]);
        }
        else
        {
            return response()->json([
                'status' => false,
                'message' => 'Dokumen dgn status Revised tidak ditemukan'
            ]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Sales $sales)
    {
        DB::beginTransaction();

        // update header
        $update_header1 = Sales::where('id', '=', $request->id)->where('flow_seq', '=', 1)->first(); // chek flow sequent, status harus new entry bukan posted
        if ($update_header1) {
            // $update_header->date_header = $request['date_header'];
            // $update_header->id_lokasi = $request['id_lokasi']; // lokasi ikut terupdate
            // $update_header->id_user = $request['id_user']; // user ikut terupdate
            // $update_header->save();
            $update_header = Sales::where('id', '=', $request->id)->where('flow_seq', '=', 1)->update([
                'date_header' => $request['date_header'],
                'location_id' => $request['location_id'],
                'customer_id' => $request['customer_id'],
                // 'subtotal' => $request['subtotal'],// dicalculate di bawah
                'subtotal' => 0,
                'disc_value' => $request['disc_value'],
                'disc_percent' => $request['disc_percent'],
                // 'disc_percentvalue' => $request['disc_percentvalue'],
                'disc_percentvalue' => 0,
                'extra_charge' => $request['extra_charge'],
                // 'dpp' => $request['dpp'], // dicalculate di bawah
                // 'vat_type' => $request['vat_type'], // ppn blm kepake
                // 'vat_percent' => $request['vat_percent'],
                // 'vat_value' => $request['vat_value'],
                // 'grandtotal' => $request['grandtotal']
                'dpp' => 0,
                'vat_type' => 0,
                'vat_percent' => 0,
                'vat_value' => 0,
                'grandtotal' => 0
            ]);
            if (!$update_header) {
                return response()->json([
                    'status' => false,
                    'message' => 'Gagal diupdate..'
                ]);
            }

            // data detail diupdate // tested
            // $update_details = json_decode($request->update_barang, true); // decode dulu
            // $update_details = json_decode($request->update_detail, true);
            $update_details = $request->update_detail;
            if ($update_details) {
                for ($i = 0; $i < count($update_details); $i++) {

                    $update_detail = SalesDetail::where('id', $update_details[$i]['id'])->first();
                    if ($update_detail) {
                        $update_detail->id_product = $update_details[$i]['id_product'];
                        $update_detail->qty = $update_details[$i]['qty'];
                        // $update_detail->keterangan = $update_details[$i]['keterangan'];
                        $update_detail->price = $update_details[$i]['product']['product_price'];
                        $update_detail->margin = $update_details[$i]['margin'];
                        $update_detail->disc_value = $update_details[$i]['disc_value'];
                        $update_detail->total_price = $update_details[$i]['product']['product_price'] * $update_details[$i]['qty'] - $update_details[$i]['disc_value'];
                        // $update_detail->vat_value = $update_details[$i]['vat_value'];
                        // $update_detail->disc_percent = $update_details[$i]['disc_percent'];
                        // $update_detail->vat_percent = $update_details[$i]['vat_percent'];

                        $update_detail->save();
                    }

                    if (!$update_detail) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Update data.'
                        ], 422);
                    }
                }
            }

            // data detail didelete
            // $delete_barangs = json_decode($request->delete_barang, true);
            // $delete_details = json_decode($request->delete_detail, true);
            $delete_details = $request->delete_detail;
            if ($delete_details) {
                for ($i = 0; $i < count($delete_details); $i++) {
                    $delete_detail = SalesDetail::where('id', $delete_details[$i]['id'])->delete();

                    if (!$delete_detail) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Delete data'
                        ], 422);
                    }
                }
            }

            // data detail dicreate
            // $create_barangs = json_decode($request->create_barang, true);
            // $create_details = json_decode($request->create_detail, true);
            $create_details = $request->create_detail;
            if ($create_details) {
                for ($i = 0; $i < count($create_details); $i++) {
                    $create_detail = SalesDetail::create([
                        'id_header' => $request->id,
                        'id_product' => $create_details[$i]['id_product'],
                        'qty' => $create_details[$i]['qty'],
                        // 'keterangan' => $create_details[$i]['keterangan'],
                        'price' => $create_details[$i]['product']['product_price'],
                        'disc_value' => $create_details[$i]['disc_value'],
                        'total_price' => $create_details[$i]['product']['product_price'] * $create_details[$i]['qty'] - $create_details[$i]['disc_value'],
                        // 'vat_value' => $create_details[$i]['vat_value'],
                        // 'disc_percent' => $create_details[$i]['disc_percent'],
                        // 'vat_percent' => $create_details[$i]['vat_percent'],
                    ]);

                    if (!$create_detail) {
                        DB::rollBack();
                        return response()->json([
                            'status' => false,
                            'message' => 'Gagal Create data'
                        ], 422);
                    }
                }
            }
        }

        if (!$update_header1) {
            DB::rollBack();
        }
        DB::commit();

        // sales invoice calculate
        DB::select('call SalesInvoice_CalculateTotal(?)', array($update_header1->id));

        return response()->json([
            'status' => $update_header1 ? true : false,
            'message' => $update_header1 ? 'Berhasil di update' : 'Gagal diupdate'
        ]);
    }

    // tidak terpakai
    public function create_return_item(Request $request)
    {
        $check_header = Sales::where('id','=',$request->id_header)->where('flow_seq','=','100')->first();

        if($check_header)
        {
            // dd($check_header->no_header);
            if($check_header->no_header == "")
            {
                return response()->json([
                    'status' => false,
                    'message' => 'Belum terdapat nomor nota!'
                ]);
            }else {
                $create_return = SalesReturn::create([
                    'id_header' => $request->id_header,
                    'id_product' => $request->id_product,
                    'qty' => $request->qty,
                ]);
                return response()->json([
                    'status' => $create_return ? true : false,
                    'message' => $create_return ? 'Berhasil buat retur' : 'Gagal buat retur barang'
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Status tidak sesuai, harus Recorded'
        ]);

        $create_return = SalesReturn::create([
            'id_header' => $request->id_header,
            'id_product' => $request->id_product,
            'qty' => $request->qty,
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Sales  $sales
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Delete hanya diperuntukkan header dgn status New Entry (1), jika 10 maka akan ditolak
        DB::beginTransaction();
        $deleted = Sales::where('id', '=', $request->id)->where('flow_seq', '=', 1)->delete(); // chek flow sequent,  status harus new entry bukan posted
        if (!$deleted) {
            // DB::table('faktur_masuk_d')->where('id_header', $id)->delete(); // tidak perlu Delete detail, sdh otomatis ada cascade
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $deleted ? true : false,
            'message' => $deleted ? 'Berhasil di hapus' : 'Gagal dihapus'

        ]);
    }

    public function postingLogTransaksi(Request $request){
        $updbegflow1 = new DocFlowController();
            $content1 = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $request->doc_id,
                'flow_prev' => 1,
                'flow_next' => 10
            ]);
            $update = $updbegflow1->updateFlow($content1);

            //posting
            $content1 = new Request([
                'doctype_id' => 5,
                'user_id' => $request->user_id,
                'doc_id' => $request->doc_id,
                'flow_prev' => 10,
                'flow_next' => 100
            ]);
            $update = $updbegflow1->updateFlow($content1);

            return response()->json([
                'status' => $update['status'],
                'message' => $update['status'] == false ? $update['update_log'] : 'Pembayaran Sukses!'
            ]);
    }
}
