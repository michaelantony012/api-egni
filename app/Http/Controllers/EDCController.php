<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\EDC;
use Illuminate\Http\Request;
use App\Models\CashierPayout;

use Illuminate\Support\Facades\DB;
use App\Http\Resources\CashierPayoutResource;

class EDCController extends Controller
{

    public function index()
    {
        $data = EDC::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => $data,
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function create(Request $request)
    {
        DB::beginTransaction();
        $add = EDC::create([
            'no_rekening' => $request->no_rekening,
            'nama_rekening' => $request->nama_rekening,
            'charge_percentage' => $request->charge_percentage
        ]);
        // DB::rollBack(); // testing
        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'id' => $add->id // id header document
        ]);
    }
    public function show($id)
    {
        $data = EDC::where('id', '=', $id)->get();

        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => $data,
            'message' => $data ? 'Data berhasil di dapat' : 'Tidak ada data'
        ]);
    }
    public function update(Request $request, EDC $EDC)
    {
        DB::beginTransaction();

        $update = EDC::where('id', '=', $request->id)->first();
        if ($update) {
            $update_data = EDC::where('id', '=', $request->id)->update([
                'no_rekening' => $request['no_rekening'],
                'nama_rekening' => $request['nama_rekening'],
                'charge_percentage' => $request['charge_percentage'],
            ]);
        }
        if (!$update_data) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $update_data ? true : false,
            'message' => $update_data ? 'Berhasil di update' : 'Gagal diupdate'
        ]);
    }
    public function destroy(Request $request)
    {
        DB::beginTransaction();
        $deleted = EDC::where('id', '=', $request->id)->delete();
        if (!$deleted) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $deleted ? true : false,
            'message' => $deleted ? 'Berhasil di hapus' : 'Gagal dihapus'

        ]);
    }

    public function Report(Request $request){
        $saldo = DB::table('sales_invoice_h as a')
                ->select(DB::raw('IFNULL(SUM(a.edc_charge_nominal),0) AS edc_nominal'))
                ->whereRaw('a.edc_charge_nominal>0
                and a.flow_seq between 100 and 120
                and year(a.date_header)=? and month(a.date_header)=?',[$request->year,$request->month])
                ->get();
        return response()->json([
            'status' => collect($saldo)->isNotEmpty() ? true : false,
            'data' => collect($saldo),
            'message' => 'Data berhasil di dapat'
        ]);
    }
}
