<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\BeginningStockResource;
use App\Models\BeginningStock;
use App\Models\BeginningStockDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;


class BeginningStockController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = BeginningStock::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => BeginningStockResource::collection($data),
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
        DB::beginTransaction();
        $add_purchase_header = BeginningStock::create([
            'date_header' => Carbon::now()->format('Y-m-d'),
            'no_header' => "", //dibuat otomatis pada saat posting
            'user_id' => $request->user_id,
            'location_id' => $request->location_id,

        ]);
        // $detail = json_decode($request->detail, true); // decode ke array dulu
        $detail = $request->detail; // decode ke array dulu
        for ($i = 0; $i < count($detail); $i++) {
            $add_purchase_detail = BeginningStockDetail::create([
                'id_header' => $add_purchase_header->id,
                'id_product' => $detail[$i]['id_product'],
                'qty' => $detail[$i]['qty'],
                'keterangan' => $detail[$i]['keterangan'],
                'price' => $detail[$i]['price'],
                'total_price' => $detail[$i]['total_price'],

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = BeginningStock::findOrFail($id);

        return response()->json([
            'data' => new BeginningStockResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        DB::beginTransaction();

        // update header
        $update_header1 = BeginningStock::where('id', '=', $request->id)->where('flow_seq', '=', 1)->first(); // chek flow sequent, status harus new entry bukan posted
        if ($update_header1) {
            // $update_header->date_header = $request['date_header'];
            // $update_header->id_lokasi = $request['id_lokasi']; // lokasi ikut terupdate
            // $update_header->id_user = $request['id_user']; // user ikut terupdate
            // $update_header->save();
            $update_header = BeginningStock::where('id', '=', $request->id)->where('flow_seq', '=', 1)->update([
                // 'no_header' => $request['no_header'],
                // 'type' => $request['type'],
                // 'date_header' => $request['date_header'],
                'location_id' => $request['location_id'],
                'keterangan' => 'Stock Awal',
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

                    $update_detail = BeginningStockDetail::where('id', $update_details[$i]['id'])->first();
                    if ($update_detail) {
                        $update_detail->id_product = $update_details[$i]['id_product'];
                        $update_detail->qty = $update_details[$i]['qty'];
                        $update_detail->keterangan = 'Stock Awal';
                        $update_detail->price = $update_details[$i]['price'];
                        $update_detail->total_price = $update_details[$i]['total_price'];

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
                    $delete_detail = BeginningStockDetail::where('id', $delete_details[$i]['id'])->delete();

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
                    $create_detail = BeginningStockDetail::create([
                        'id_header' => $request->id,
                        'id_product' => $create_details[$i]['id_product'],
                        'qty' => $create_details[$i]['qty'],
                        'keterangan' => 'Stock Awal',
                        'price' => $create_details[$i]['price'],
                        'total_price' => $create_details[$i]['total_price'],

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        // Delete hanya diperuntukkan header dgn status New Entry (1), jika 10 maka akan ditolak
        DB::beginTransaction();
        $deleted = BeginningStock::where('id', '=', $request->id)->where('flow_seq', '=', 1)->delete(); // chek flow sequent,  status harus new entry bukan posted
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
