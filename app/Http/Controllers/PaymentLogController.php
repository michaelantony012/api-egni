<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\PaymentLog;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PaymentLogResource;

class PaymentLogController extends Controller
{
    public function index()
    {
        $data = PaymentLog::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => PaymentLogResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function create(Request $request)
    {
        DB::beginTransaction();
        $add = PaymentLog::create([
            'id_method' => $request->id_method,
            'ref_no' => $request->ref_no,
            'value' => $request->value,
            'charge_value' => $request->charge_value
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
        $data = PaymentLog::findOrFail($id);

        return response()->json([
            'data' => new PaymentLogResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function update(Request $request, PaymentLog $paymentLog)
    {
        DB::beginTransaction();

        $update = PaymentLog::where('id', '=', $request->id)->first();
        if ($update) {
            $update_data = PaymentLog::where('id', '=', $request->id)->update([
                'id_method' => $request['id_method'],
                'ref_no' => $request['ref_no'],
                'value' => $request['value'],
                'charge_value' => $request['charge_value']
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
        $deleted = PaymentLog::where('id', '=', $request->id)->delete();
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
