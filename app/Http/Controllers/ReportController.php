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
                WHERE trans_date = ? AND doctype_id=2 and (a.id_product=? or 0=?) and trans_loc = ?
                GROUP BY product_code, trans_date, doctype_id, product_name
                UNION ALL
                SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                WHERE trans_date = ? AND doctype_id=4 and (a.id_product=? or 0=?) and trans_loc = ?
                GROUP BY product_code, trans_date, doctype_id, product_name
                HAVING SUM(trans_qty)<0';
        $stockkeluar = DB::select($sqlstockkeluar,[$request->end_date,$id_product,$id_product,$request->id_lokasi,$request->end_date,$id_product,$id_product,$request->id_lokasi]);

        $newArray['stocKeluar'] = $stockkeluar;

        //Omset
        $sqlomset = 'SELECT SUM(grandtotal) AS omset, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(100,120) AND date_header = ? and location_id = ?
                GROUP BY date_header
                HAVING SUM(grandtotal)>0';
        $omset = DB::select($sqlomset,[$request->end_date, $request->id_lokasi]);
        
        $newArray['omset'] = $omset;

        // return response()->json([
        //     'status' => collect($stmt)->isNotEmpty() ? true : false,
        //     'data' => collect($stmt),
        //     'message' => 'Data berhasil di dapat'
        // ]);

        $sqlpot = 'SELECT SUM(disc_value) AS potongan, date_header FROM sales_invoice_h a
                WHERE flow_seq IN(100,120) AND date_header = ? and location_id = ?
                GROUP BY date_header
                HAVING SUM(disc_value)>0';
        $potongan = DB::select($sqlpot,[$request->end_date, $request->id_lokasi]);

        
        
        $newArray['potongan'] = $potongan;

        return response()->json([
            'status' => collect($newArray)->isNotEmpty() ? true : false,
            'data' => $newArray,
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function reportdashboard2(Request $request){
        $newArray = array();
        //Stock Keluar
        if(empty($request->id_product)){
            $id_product=0;
        }else{
            $id_product = $request->id_product;
        }
        $sqlstockkeluar = 'SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id, loc_name 
                FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                INNER JOIN locations c on a.trans_loc=c.id
                WHERE trans_date = ? AND doctype_id=2 and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name, a.trans_loc, loc_name
                UNION ALL
                SELECT product_code, product_name, SUM(trans_qty) AS stock_keluar, trans_date, doctype_id, loc_name 
                FROM inventory_journal a
                INNER JOIN products b ON a.`id_product`=b.`id`
                INNER JOIN locations c on a.trans_loc=c.id
                WHERE trans_date = ? AND doctype_id=4 and (a.id_product=? or 0=?)
                GROUP BY product_code, trans_date, doctype_id, product_name, a.trans_loc, loc_name 
                HAVING SUM(trans_qty)<0';
        $stockkeluar = DB::select($sqlstockkeluar,[$request->end_date,$id_product,$id_product,$request->end_date,$id_product,$id_product]);

        $newArray['stocKeluarperloc'] = $stockkeluar;

        //Omset
        $sqlomset = 'SELECT SUM(grandtotal) AS omset, date_header, loc_name 
                FROM sales_invoice_h a
                INNER JOIN locations b on a.location_id=b.id
                WHERE flow_seq IN(100,120) AND date_header = ?
                GROUP BY date_header,location_id, loc_name
                HAVING SUM(grandtotal)>0';
        $omset = DB::select($sqlomset,[$request->end_date]);
        
        $newArray['omsetperloc'] = $omset;


        //potongan
        $sqlpot = 'SELECT SUM(disc_value) AS potongan, date_header, loc_name 
                FROM sales_invoice_h a
                INNER JOIN locations b on a.location_id=b.id
                WHERE flow_seq IN(100,120) AND date_header = ? 
                GROUP BY date_header,location_id, loc_name
                HAVING SUM(disc_value)>0';
        $potongan = DB::select($sqlpot,[$request->end_date]);
        
        $newArray['potonganperloc'] = $potongan;

        //aset
        $sqlaset = 'SELECT SUM(product_price*stock) AS aset, trans_loc, loc_name
                    FROM products a
                    INNER JOIN (
                        SELECT SUM(trans_qty) AS stock,id_product,trans_loc
                        FROM inventory_journal 
                        WHERE trans_date <= ?
                        GROUP BY id_product, trans_loc
                        HAVING SUM(trans_qty)>0
                    )b ON a.id=b.id_product
                    INNER JOIN locations c ON b.trans_loc=c.id
                    WHERE a.primary_stock=1
                    GROUP BY trans_loc, loc_name
                ';
        $aset = DB::select($sqlaset,[$request->end_date]);
        
        $newArray['asetperloc'] = $aset;

        return response()->json([
            'status' => collect($newArray)->isNotEmpty() ? true : false,
            'data' => $newArray,
            'message' => 'Data berhasil di dapat'
        ]);
    }
}
