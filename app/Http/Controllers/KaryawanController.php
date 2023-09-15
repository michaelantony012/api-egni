<?php

namespace App\Http\Controllers;
use App\Models\Karyawan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KaryawanController extends Controller
{
    public function index()
    {
        $data = Karyawan::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => $data,
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function create(Request $request)
    {
        // $create = Customer::create($request->all());
        $create = Karyawan::create([
            'empl_name' => $request->empl_name,
            'empl_address' => $request->empl_address,
            'empl_enabled' => $request->empl_enabled,
            'empl_nik' => $request->empl_nik
        ]);
        return response()->json([
            'status' => $create ? true : false,
            'data' => $create,
            'message' => $create ? 'Berhasil' : 'Gagal'
        ]);
    }

    public function show($id)
    {
        $data = Karyawan::findOrFail($id);
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => $data,
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function update(Request $request, Karyawan $karyawan)
    {
        $updated = DB::table('employes')
            ->where('id', $request->id)
            ->update([
                'empl_name' => $request->empl_name,
                'empl_address' => $request->empl_address,
                'empl_enabled' => $request->empl_enabled,
                'empl_nik' => $request->empl_nik,
            ]);

        return response()->json([
            'status' => $updated ? true : false,
            'message' => $updated ? 'Berhasil' : 'Gagal'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Karyawan  $karyawan
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = Karyawan::destroy($id);
        return response()->json([
            'status' => $delete ? true : false,
            'message' => $delete ? 'Berhasil' : 'Gagal'
        ]);
    }
}
