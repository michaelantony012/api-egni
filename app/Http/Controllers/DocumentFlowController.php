<?php

namespace App\Http\Controllers;
use App\Models\DocumentType;
use App\Models\DocumentFlow;
use App\Models\DocumentFlowLogic;
use App\Http\Resources\DocumentFlowResource;
use DB;

use Illuminate\Http\Request;

class DocumentFlowController extends Controller
{
    //
    public function index()
    {
        $data = DocumentType::all();
        return response()->json([
            'status' => collect($data)->isNotEmpty() ? true : false,
            'data' => DocumentFlowResource::collection($data),
            'message' => 'Data berhasil di dapat'
        ]);
    }

    public function create(Request $request){
        $c_document_type = json_decode($request->c_document_type,200);
        DB::beginTransaction();
        $header = DocumentType::create([
            'id'         =>  $c_document_type['id'],
            'doctype_no' =>  $c_document_type['doctype_no'],
            'doctype_desc' =>  $c_document_type['doctype_desc'],
        ]);

        //CREATE DETAIL
        $c_document_flows = json_decode($request->c_document_flow,200);
        if($c_document_flows){
            for ($i = 0; $i < count($c_document_flows); $i++) {
                $c_document_flow = DocumentFlow::create([
                    'doctype_id' => $c_document_type['id'],
                    'doc_flow' => $c_document_flows[$i]['doc_flow'],
                    'flow_desc' => $c_document_flows[$i]['flow_desc'],
                ]);

                if (!$c_document_flow) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        $c_document_flow_logics = json_decode($request->c_document_flow_logic,200);
        if($c_document_flow_logics){
            for ($i = 0; $i < count($c_document_flow_logics); $i++) {
                $c_document_flow_logic = DocumentFlowLogic::create([
                    'doctype_id' => $c_document_type['id'],
                    'flow_prev' => $c_document_flow_logics[$i]['flow_prev'],
                    'flow_next' => $c_document_flow_logics[$i]['flow_next'],
                    'flow_desc' => $c_document_flow_logics[$i]['flow_desc'],
                ]);

                if (!$c_document_flow_logic) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Logic Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        if (!$header) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $header ? true : false,
            'message' => $header ? 'Document Flow berhasil ditambah' : 'Document Flow gagal ditambah'
        ]);
    }

    public function update(Request $request) {
        $u_document_type = json_decode($request->u_document_type,200);
        //UPDATE HEADER
        DB::beginTransaction();
        $update_header = DocumentType::where('id',$u_document_type['id'])->update([
            'id' => $u_document_type['id'],
            'doctype_no' => $u_document_type['doctype_no'],
            'doctype_desc' => $u_document_type['doctype_desc']
        ]);

        //UPDATE DETAIL
        $u_document_flows = json_decode($request->u_document_flow,true);
        if($u_document_flows){
            for ($i = 0; $i < count($u_document_flows); $i++) {
                $u_document_flow = DocumentFlow::where('id',$u_document_flows[$i]['id'])->first();
                if($u_document_flow){
                    $u_document_flow->doctype_id = $u_document_type['id'];
                    $u_document_flow->doc_flow = $u_document_flows[$i]['doc_flow'];
                    $u_document_flow->flow_desc = $u_document_flows[$i]['flow_desc'];
                    $u_document_flow->save();
                }

                if (!$u_document_flow) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        $u_document_flow_logics = json_decode($request->u_document_flow_logic,true);
        if($u_document_flow_logics){
            for ($i = 0; $i < count($u_document_flow_logics); $i++) {
                $u_document_flow_logic = DocumentFlowLogic::where('id',$u_document_flow_logics[$i]['id'])->first();
                if($u_document_flow_logic){
                    $u_document_flow_logic->doctype_id = $u_document_type['id'];
                    $u_document_flow_logic->flow_prev = $u_document_flow_logics[$i]['flow_prev'];
                    $u_document_flow_logic->flow_next = $u_document_flow_logics[$i]['flow_next'];
                    $u_document_flow_logic->flow_desc = $u_document_flow_logics[$i]['flow_desc'];
                    $u_document_flow_logic->save();
                }

                if (!$u_document_flow_logic) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Logic Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        //CREATE DETAIL
        $c_document_flows = json_decode($request->c_document_flow,true);
        if($c_document_flows){
            for ($i = 0; $i < count($c_document_flows); $i++) {
                $c_document_flow = DocumentFlow::create([
                    'doctype_id' => $u_document_type['id'],
                    'doc_flow' => $c_document_flows[$i]['doc_flow'],
                    'flow_desc' => $c_document_flows[$i]['flow_desc'],
                ]);

                if (!$c_document_flow) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        $c_document_flow_logics = json_decode($request->c_document_flow_logic,true);
        if($c_document_flow_logics){
            for ($i = 0; $i < count($c_document_flow_logics); $i++) {
                $c_document_flow_logic = DocumentFlowLogic::create([
                    'doctype_id' => $u_document_type['id'],
                    'flow_prev' => $c_document_flow_logics[$i]['flow_prev'],
                    'flow_next' => $c_document_flow_logics[$i]['flow_next'],
                    'flow_desc' => $c_document_flow_logics[$i]['flow_desc'],
                ]);

                if (!$c_document_flow_logic) {
                    DB::rollBack();
                    return response()->json([
                        'status' => false,
                        'message' => 'Data Document Flow Logic Berhasil disimpan!'
                    ], 422);
                }
            }
        }

        if (!$update_header) {
            DB::rollBack();
        }
        DB::commit();
        return response()->json([
            'status' => $update_header ? true : false,
            'message' => $update_header ? 'Document Flow berhasil diupdate' : 'Document Flow gagal diupdate'
        ]);
    }

}
