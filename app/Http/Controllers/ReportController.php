<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    //
    public function stockKeluar(Request $request){
        if(empty($request->id_product)){
            $id_product=0;
        }else{
            $id_product = $request->id_product;
        }
        $sql = 'SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                WHERE trans_date BETWEEN ? AND ? AND doctype_id IN(2,5) and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name
                UNION ALL
                SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                WHERE trans_date BETWEEN ? AND ? AND doctype_id=4 and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name
                HAVING SUM(trans_qty)<0';
        $stmt = DB::select($sql,[$request->start_date,$request->end_date,$id_product,$id_product,$request->start_date,$request->end_date,$id_product,$id_product]);

        return response()->json([
            'status' => collect($stmt)->isNotEmpty() ? true : false,
            'data' => collect($stmt),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function omset(Request $request){
        $sql = 'SELECT SUM(grandtotal) AS omset, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(110,120) AND date_header BETWEEN ? AND ?
                GROUP BY date_header';
        $stmt = DB::select($sql,[$request->start_date,$request->end_date]);

        return response()->json([
            'status' => collect($stmt)->isNotEmpty() ? true : false,
            'data' => collect($stmt),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function potongan(Request $request){
        $sql = 'SELECT SUM(grandtotal) AS omset, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(110,120) AND date_header BETWEEN ? AND ?
                GROUP BY date_header';
        $stmt = DB::select($sql,[$request->start_date,$request->end_date]);

        return response()->json([
            'status' => collect($stmt)->isNotEmpty() ? true : false,
            'data' => collect($stmt),
            'message' => 'Data berhasil di dapat'
        ]);
    }
}
