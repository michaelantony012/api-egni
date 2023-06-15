<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Carbon\Carbon;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PaymentMethodResource;

class PaymentMethodController extends Controller
{
    
    public function index()
    {
        $data = PaymentMethod::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => PaymentMethodResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function create(Request $request)
    {
        DB::beginTransaction();
        $add = PaymentMethod::create([
            'payment_name' => $request->payment_name,
            'payment_charge' => $request->payment_charge,
            'account_number' => $request->account_number,
            'account_name' => $request->account_name,
            'bank' => $request->bank
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
        $data = PaymentMethod::findOrFail($id);

        return response()->json([
            'data' => new PaymentMethodResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function update(Request $request, PaymentMethod $paymentMethod)
    {
        DB::beginTransaction();

        $update = PaymentMethod::where('id', '=', $request->id)->first();
        if ($update) {
            $update_data = PaymentMethod::where('id', '=', $request->id)->update([
                'payment_name' => $request['payment_name'],
                'payment_charge' => $request['payment_charge'],
                'account_number' => $request['account_number'],
                'account_name' => $request['account_name'],
                'bank' => $request['bank']
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
        $deleted = PaymentMethod::where('id', '=', $request->id)->delete();
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
