<?php

namespace App\Http\Controllers;

use App\Http\Resources\PurchasingResource;
use App\Models\Purchasing;
use Illuminate\Http\Request;
use App\Models\DocFlowLogic;
use DB;
class PurchasingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Purchasing::Join('document_flow as b',function($join){
            $join->on('purchase_invoice_h.doctype_id','b.doctype_id')
                ->on('purchase_invoice_h.flow_seq','b.doc_flow');
            })->select('purchase_invoice_h.*','b.flow_desc')
            ->get();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => PurchasingResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }
    public function detailIndex($id)
    {
        # code...
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = Purchasing::findOrFail($id);
        return response()->json([
            'data' => new PurchasingResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function edit(Purchasing $purchasing)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Purchasing $purchasing)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Purchasing  $purchasing
     * @return \Illuminate\Http\Response
     */
    public function destroy(Purchasing $purchasing)
    {
        //
    }

}
