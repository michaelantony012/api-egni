<?php

namespace App\Http\Controllers;
use Carbon\Carbon;
use App\Models\CashierPayout;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\CashierPayoutResource;

use Illuminate\Http\Request;

class CashierPayoutController extends Controller
{
    
    public function index()
    {
        $data = CashierPayout::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => CashierPayoutResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function create(Request $request)
    {
        DB::beginTransaction();
        $add = CashierPayout::create([
            'id_user' => $request->user_id,
            'cash_in' => $request->cash_in, // saldo awal kasir
            'cash_out' => $request->cash_in, // transaksi kasir, awalan sama dgn cash_in
            'online_payment' => 0 // transaksi kasir, awalan pasti 0
        ]);
        // DB::rollBack(); // testing
        DB::commit();

        if($request->is_posting)
        {
            // recording
            $updbegflow1 = new DocFlowController();
            $content1 = new Request([
                'doctype_id' => 5,
                'doc_id' => $add->id,
                'flow_prev' => 1,
                'flow_next' => 10
            ]);
            $updbegflow1->updateFlow($content1);
            
            // posting
            $content1 = new Request([
                'doctype_id' => 5,
                'doc_id' => $add->id,
                'flow_prev' => 10,
                'flow_next' => 100
            ]);
            $updbegflow1->updateFlow($content1);
        }
        else if(!$request->is_posting)
        {
            // recording
            $updbegflow1 = new DocFlowController();
            $content1 = new Request([
                'doctype_id' => 5,
                'doc_id' => $add->id,
                'flow_prev' => 1,
                'flow_next' => 10
            ]);
            $updbegflow1->updateFlow($content1);
        }

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'id_header' => $add->id // id header document
        ]);
    }
    public function show($id_user)
    {
        $data = CashierPayout::where('id_user', '=', $id_user)->whereDate('created_at', Carbon::today())->get();

        return response()->json([
            'status' => $data ? true : false,
            'data' => $data ? new CashierPayoutResource($data) : "",
            'message' => $data ? 'Data berhasil di dapat' : 'Tidak ada data'
        ]);
    }
    public function update(Request $request, CashierPayout $cashierPayout)
    {
        DB::beginTransaction();

        $update = CashierPayout::where('id', '=', $request->id)->first();
        if ($update) {
            $update_data = CashierPayout::where('id', '=', $request->id)->update([
                'id_user' => $request['id_user'],
                'cash_in' => $request['cash_in'],
                // 'cash_out' => $request['cash_out'],
                // 'online_payment' => $request['online_payment']
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
        $deleted = CashierPayout::where('id', '=', $request->id)->delete();
        if (!$deleted) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $deleted ? true : false,
            'message' => $deleted ? 'Berhasil di hapus' : 'Gagal dihapus'

        ]);
    }
}
