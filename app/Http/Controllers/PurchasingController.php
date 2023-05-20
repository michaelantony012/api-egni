<?php

namespace App\Http\Controllers;

use App\Models\Purchasing;
use Illuminate\Http\Request;
use App\Models\PurchasingDetail;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PurchasingResource;
use App\Http\Resources\d_PurchasingResource;
use App\Models\DocFlow;
use App\Models\Product;

class PurchasingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Purchasing::Join('document_flow as b', function ($join) {
            $join->on('purchase_invoice_h.doctype_id', 'b.doctype_id')
                ->on('purchase_invoice_h.flow_seq', 'b.doc_flow');
        })->select('purchase_invoice_h.*', 'b.flow_desc')
            ->get();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => PurchasingResource::collection($data),
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
        $add_purchase_header = Purchasing::create([
            'date_header' => $request->date_header,
            'no_header' => "", //dibuat otomatis pada saat posting
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,
            'supplier_id' => $request->supplier_id
        ]);
        $detail = json_decode($request->detail, true); // decode ke array dulu
        for ($i = 0; $i < count($detail); $i++) {
            $add_purchase_detail = PurchasingDetail::create([
                'id_header' => $add_purchase_header->id,
                'id_product' => $detail[$i]['id_product'],
                'qty' => $detail[$i]['qty'],
                'keterangan' => $detail[$i]['keterangan'],
                'price' => $detail[$i]['price'],
                'disc_value' => $detail[$i]['disc_value'],
                'total_price' => $detail[$i]['total_price'],
                'vat_value' => $detail[$i]['vat_value'],
                'disc_percent' => $detail[$i]['disc_percent'],
                'vat_percent' => $detail[$i]['vat_percent'],
            ]);

            if (!$add_purchase_detail || !$add_purchase_header) {
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
            'id_header' => $add_purchase_header->id // id header document
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
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Purchasing::findOrFail($id);

        return response()->json([
            'data' => new PurchasingResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
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
        DB::beginTransaction();

        // update header
        $update_header1 = Purchasing::where('id', '=', $request->id)->where('flow_seq', '=', 1)->first(); // chek flow sequent, status harus new entry bukan posted
        if ($update_header1) {
            // $update_header->date_header = $request['date_header'];
            // $update_header->id_lokasi = $request['id_lokasi']; // lokasi ikut terupdate
            // $update_header->id_user = $request['id_user']; // user ikut terupdate
            // $update_header->save();
            $update_header = Purchasing::where('id', '=', $request->id)->where('flow_seq', '=', 1)->update([
                'date_header' => $request['date_header'],
                'location_id' => $request['location_id'],
                'supplier_id' => $request['supplier_id']
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

                    $update_detail = PurchasingDetail::where('id', $update_details[$i]['id'])->first();
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
                    $delete_detail = PurchasingDetail::where('id', $delete_details[$i]['id'])->delete();

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
                    $create_detail = PurchasingDetail::create([
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
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Delete hanya diperuntukkan header dgn status New Entry (1), jika 10 maka akan ditolak
        DB::beginTransaction();
        $deleted = Purchasing::where('id', '=', $request->id)->where('flow_seq', '=', 1)->delete(); // chek flow sequent,  status harus new entry bukan posted
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
