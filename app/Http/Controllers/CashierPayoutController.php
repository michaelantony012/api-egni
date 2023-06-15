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
            'cash_in' => $request->cash_in,
            'cash_out' => $request->cash_out,
            'online_payment' => $request->online_payment
        ]);
        // DB::rollBack(); // testing
        DB::commit();
        return response()->json([
            'status' => true,
            'message' => 'Data berhasil ditambahkan',
            'id_header' => $add->id // id header document
        ]);
    }
    public function show($id)
    {
        $data = CashierPayout::findOrFail($id);

        return response()->json([
            'data' => new CashierPayoutResource($data),
            'message' => 'Data berhasil di dapat'
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
                'cash_out' => $request['cash_out'],
                'online_payment' => $request['online_payment']
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
