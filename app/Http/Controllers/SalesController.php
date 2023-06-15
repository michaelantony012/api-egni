<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Sales;
use App\Models\DocFlow;
use App\Models\Product;
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
    public function index()
    {
        $data = Sales::Join('document_flow as b', function ($join) {
            $join->on('sales_invoice_h.doctype_id', 'b.doctype_id')
                ->on('sales_invoice_h.flow_seq', 'b.doc_flow');
        })->select('sales_invoice_h.*', 'b.flow_desc')
            ->get();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
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
            'date_header' => Carbon::now()->format('Y-m-d'),//$request->date_header,
            'no_header' => "", //dibuat otomatis pada saat posting
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'customer_id' => $request->customer_id,
            'subtotal' => $request->subtotal,
            'disc_value' => $request->disc_value,
            'disc_percent' => $request->disc_percent,
            'disc_percentvalue' => $request->disc_percentvalue,
            'extra_charge' => $request->extra_charge,
            'dpp' => $request->dpp,
            'vat_type' => $request->vat_type,
            'vat_percent' => $request->vat_percent,
            'vat_value' => $request->vat_value,
            'grandtotal' => $request->grandtotal,
        ]);
        $detail = $request->detail;// decode ke array dulu
        // $detail = json_decode($request->detail, true); // decode ke array dulu
        for ($i = 0; $i < count($detail); $i++) {
            $add_sales_detail = SalesDetail::create([
                'id_header' => $add_sales_header->id,
                'id_product' => $detail[$i]['id_product'],
                'qty' => $detail[$i]['qty'],
                'keterangan' => $detail[$i]['keterangan'],
                'margin' => $detail[$i]['margin'],
                'price' => $detail[$i]['price'],
                'disc_value' => $detail[$i]['disc_value'],
                'total_price' => $detail[$i]['total_price'],
                'vat_value' => $detail[$i]['vat_value'],
                'disc_percent' => $detail[$i]['disc_percent'],
                'vat_percent' => $detail[$i]['vat_percent'],
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
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'id_header' => $add_sales_header->id // id header document
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
    public function show($id)
    {
        $data = Sales::findOrFail($id);

        return response()->json([
            'data' => new SalesResource($data),
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
                'subtotal' => $request['subtotal'],
                'disc_value' => $request['disc_value'],
                'disc_percent' => $request['disc_percent'],
                'disc_percentvalue' => $request['disc_percentvalue'],
                'extra_charge' => $request['extra_charge'],
                'dpp' => $request['dpp'],
                'vat_type' => $request['vat_type'],
                'vat_percent' => $request['vat_percent'],
                'vat_value' => $request['vat_value'],
                'grandtotal' => $request['grandtotal']
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
                        $update_detail->keterangan = $update_details[$i]['keterangan'];
                        $update_detail->price = $update_details[$i]['price'];
                        $update_detail->margin = $update_details[$i]['margin'];
                        $update_detail->disc_value = $update_details[$i]['disc_value'];
                        $update_detail->total_price = $update_details[$i]['total_price'];
                        $update_detail->vat_value = $update_details[$i]['vat_value'];
                        $update_detail->disc_percent = $update_details[$i]['disc_percent'];
                        $update_detail->vat_percent = $update_details[$i]['vat_percent'];

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
                        'keterangan' => $create_details[$i]['keterangan'],
                        'price' => $create_details[$i]['price'],
                        'disc_value' => $create_details[$i]['disc_value'],
                        'total_price' => $create_details[$i]['total_price'],
                        'vat_value' => $create_details[$i]['vat_value'],
                        'disc_percent' => $create_details[$i]['disc_percent'],
                        'vat_percent' => $create_details[$i]['vat_percent'],
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

        //return
        $update_returns = $request->update_return;
        if ($update_returns) {
            for ($i = 0; $i < count($update_returns); $i++) {

                $update_return = SalesReturn::where('id', $update_returns[$i]['id'])->first();
                if ($update_return) {
                    $update_return->id_product = $update_returns[$i]['id_product'];
                    $update_return->qty = $update_returns[$i]['qty'];
                    $update_return->keterangan = $update_returns[$i]['keterangan'];
                    $update_return->price = $update_returns[$i]['price'];
                    $update_return->margin = $update_returns[$i]['margin'];
                    $update_return->disc_value = $update_returns[$i]['disc_value'];
                    $update_return->total_price = $update_returns[$i]['total_price'];
                    $update_return->vat_value = $update_returns[$i]['vat_value'];
                    $update_return->disc_percent = $update_returns[$i]['disc_percent'];
                    $update_return->vat_percent = $update_returns[$i]['vat_percent'];

                    $update_return->save();
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

        $delete_returns = $request->delete_return;
        if ($delete_returns) {
            for ($i = 0; $i < count($delete_returns); $i++) {
                $delete_return = SalesReturn::where('id', $delete_returns[$i]['id'])->delete();

                if (!$delete_return) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Gagal Delete data'
                    ], 422);
                }
            }
        }

        $create_returns = $request->create_return;
        if ($create_returns) {
            for ($i = 0; $i < count($create_returns); $i++) {
                $create_return = SalesReturn::create([
                    'id_header' => $request->id,
                    'id_product' => $create_returns[$i]['id_product'],
                    'qty' => $create_returns[$i]['qty'],
                    'keterangan' => $create_returns[$i]['keterangan'],
                    'price' => $create_returns[$i]['price'],
                    'disc_value' => $create_returns[$i]['disc_value'],
                    'total_price' => $create_returns[$i]['total_price'],
                    'vat_value' => $create_returns[$i]['vat_value'],
                    'disc_percent' => $create_returns[$i]['disc_percent'],
                    'vat_percent' => $create_returns[$i]['vat_percent'],
                ]);

                if (!$create_return) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Gagal Create data'
                    ], 422);
                }
            }
        }
        

        if (!$update_header1) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $update_header1 ? true : false,
            'message' => $update_header1 ? 'Berhasil di update' : 'Gagal diupdate'
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
}
