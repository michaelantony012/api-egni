<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocFlowResource;
use App\Models\DocFlow;
use App\Models\DocFlowLogic;
use App\Models\DocType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DocFlowController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = DocType::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => DocFlowResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        DB::beginTransaction();
        $create = DocType::create($request->c_document_type);
        foreach ($request->c_document_flow as $item) {
            DocFlow::create($item);
        }
        foreach ($request->c_document_flow_logic as $item2) {

            DocFlowLogic::create($item2);
        }


        return response()->json([
            'status' => $create ? true : false,
            'message' => 'Document Flow berhasil ditambah'
        ]);
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
     * @param  \App\Models\DocFlow  $docFlow
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $data = DocType::find($id);
        return response()->json([
            'data' => new DocFlowResource($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\DocFlow  $docFlow
     * @return \Illuminate\Http\Response
     */
    public function edit(DocFlow $docFlow)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\DocFlow  $docFlow
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, DocFlow $docFlow)
    {


        $updated = DocType::where('id', $request->u_document_type['id'])->update($request->u_document_type);

        //UPDATE DETAIL
        foreach ($request->u_document_flow as $item) {

            DocFlow::where('id', $item['id'])->update([
                'doctype_id' => $request->u_document_type['id'],
                'doc_flow' => $item['doc_flow'],
                'flow_desc' => $item['flow_desc']
            ]);
        }

        foreach ($request->u_document_flow_logic as $item2) {

            DocFlowLogic::where('id', $item2['id'])->update([
                'doctype_id' => $request->u_document_type['id'],
                'flow_prev' => $item2['flow_prev'],
                'flow_next' => $item2['flow_next'],
                'flow_desc' => $item2['flow_desc']
            ]);
        }

        //CREATE DETAIL
        foreach ($request->c_document_flow as $item) {

            DocFlow::create($item);
        }
        foreach ($request->c_document_flow_logic as $item2) {

            DocFlowLogic::create($item2);
        }

        //DELETE DETAIL
        foreach ($request->d_document_flow as $item) {

            DocFlow::where('id',$item['id'])->delete();
        }
        foreach ($request->d_document_flow_logic as $item2) {

            DocFlowLogic::where('id',$item2['id'])->delete();
        }

        return response()->json([
            'status' => $updated ? true : false,
            'message' => 'Berhasil'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\DocFlow  $docFlow
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $delete = DocFlow::destroy($id);
        return response()->json([
            'status' => $delete ? true : false,
            'message' => 'Berhasil'
        ]);
    }
}
