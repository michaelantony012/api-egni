<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Regency;
use App\Models\District;
use App\Models\Province;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class OtherController extends Controller
{
    //
    public function provinces(Request $request)
    {
        $data = Province::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'message' => 'Data provinsi berhasil di dapat',
            'data' => $data,
        ]);
    }
    public function regencies($province_id)
    {
        $data = Regency::where('province_id', $province_id)->get();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'province_id' => $province_id,
            'message' => 'Data kecamatan berhasil di dapat',
            'data' => $data,
        ]);
    }
    public function districts($regency_id)
    {
        $data = District::where('regency_id', $regency_id)->get();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'regency_id' => $regency_id,
            'message' => 'Data kelurahan berhasil di dapat',
            'data' => $data,
        ]);
    }
}
