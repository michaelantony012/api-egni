<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    //
    public function reportdashboard(Request $request){
        $newArray = array();
        //Stock Keluar
        if(empty($request->id_product)){
            $id_product=0;
        }else{
            $id_product = $request->id_product;
        }
        $sqlstockkeluar = 'SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                WHERE trans_date <= ? AND doctype_id IN(2,5) and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name
                UNION ALL
                SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                WHERE trans_date <= ? AND doctype_id=4 and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name
                HAVING SUM(trans_qty)<0';
        $stockkeluar = DB::select($sqlstockkeluar,[$request->end_date,$id_product,$id_product,$request->end_date,$id_product,$id_product]);

        $newArray['stocKeluar'] = $stockkeluar;

        //Omset
        $sqlomset = 'SELECT SUM(grandtotal) AS omset, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(110,120) AND date_header <= ?
                GROUP BY date_header';
        $omset = DB::select($sqlomset,[$request->end_date]);
        
        $newArray['omset'] = $omset;

        // return response()->json([
        //     'status' => collect($stmt)->isNotEmpty() ? true : false,
        //     'data' => collect($stmt),
        //     'message' => 'Data berhasil di dapat'
        // ]);

        $sqlpot = 'SELECT SUM(disc_value) AS potongan, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(110,120) AND date_header <= ? 
                GROUP BY date_header';
        $potongan = DB::select($sqlpot,[$request->end_date]);

        
        
        $newArray['potongan'] = $potongan;

        return response()->json([
            'status' => collect($newArray)->isNotEmpty() ? true : false,
            'data' => $newArray,
            'message' => 'Data berhasil di dapat'
        ]);
    }
}
