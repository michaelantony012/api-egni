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

    public function openEditor($id,$type){
        $trans_id = $id;
		if($type=='qcheck'){
			$getdatatype = "check";
		}else if($type=='qupdate'){
			$getdatatype = "update";
		}
        return view('ace')->with(compact('trans_id','getdatatype'));
    }

    public function getDataEditor(Request $request){
        if(request()->ajax()){
            if($request->gtype=='update'){
                $query = DocFlowLogic::where('id',$request->transid)->select('query_update')->first();
            }else if($request->gtype=='check'){
                $query = "";
            }
            return response()->json(['msg'=>$query]);
        }else{
            return response()->json(['msg'=>'No Ajax Request!']); 
        }
    }

    public function updateEditor(Request $request){
        if(request()->ajax()){
            if($request->gtype=='update'){
                $query = DocFlowLogic::where('id',$request->transid)->update(['query_update'=>$request->recordText]);
            }else if($request->gtype=='check'){
                $query = DocFlowLogic::where('id',$request->transid)->update(['query_check'=>$request->recordText]);
            }
            return response()->json(['msg'=>'Success Update!']);
        }else{
            return response()->json(['msg'=>'No Ajax Request!']); 
        }
    }

    public function updateFlow(Request $request){
        $logic = DocFlowLogic::where('doctype_id',$request->doctype_id)->where('flow_prev',$request->flow_prev)->where('flow_next',$request->flow_next)->select('query_check','query_update')->first();
        $basetable = DocType::where('id',$request->doctype_id)->select('doctype_table')->first();
        $baseheader = DB::table($basetable->doctype_table)
                        ->where('id',$request->doc_id)->first();

        $fetchdoctype = $baseheader->doctype_id;
        $fetchflowseq = $baseheader->flow_seq;

        $user = 1;
        $rs['flag'] = true;
		$rs['update_log'] = "";
        
        
        if($fetchdoctype!=$request->doctype_id || $fetchflowseq!=$request->flow_prev){
			
            $rs['flag'] = false;
            $rs['update_log'] = "Status document not matched. Please reload the document";
            
        }else{
            $query_dropcheck = "";
            if(!is_null($logic->query_check) && trim($logic->query_check)!=''){
                

                $query_dropcheck = "DROP PROCEDURE IF EXISTS `z_id".$user."`;";
                \DB::unprepared($query_dropcheck);

                $query_check = "
				CREATE PROCEDURE `z_id".$user."`(_documentid INT)
				BEGIN
					set @docid = _documentid;
					".$logic->query_check."
					SELECT @msg AS msg;
				END;";				
				\DB::unprepared($query_check);
				
				// $query_call = "CALL z_id".$user."($doc_id);";
				$temp = DB::statement('CALL z_id"'.$user.'(?)',array($request->doc_id));
				
				if($temp[0]['msg']!=""){
					$rs['flag'] = false;
					$rs['update_log'] = $temp[0]['msg'];
				}

            }

            if($rs['flag']){
                $query_dropupdate = "DROP PROCEDURE IF EXISTS `y_id".$user."`;";
                \DB::unprepared($query_dropupdate);
                
                $query_update = "
                CREATE PROCEDURE `y_id".$user."`(xdocumentid INT, xflowprev INT, xflownext INT)
                BEGIN
                
                    DECLARE EXIT HANDLER FOR SQLEXCEPTION
                    BEGIN
                        ROLLBACK;
                        SHOW ERRORS;
                    END;
                    START TRANSACTION;
                    
                    set @docid = xdocumentid;
                    set @flowprev = xflowprev;
                    set @flownext = xflownext;
                    
                    UPDATE $basetable->doctype_table SET flow_seq = @flownext where id = @docid and flow_seq = @flowprev;	
                    
                    ".(is_null($logic->query_update)?"":$logic->query_update)."				
                    
                    COMMIT;
                
                    
                END;";
                \DB::unprepared($query_update);
                \DB::statement('CALL y_id'.$user.'(?,?,?)',array($request->doc_id,$request->flow_prev,$request->flow_next));
                \DB::unprepared($query_dropupdate.$query_dropcheck);
            }
            
        }
        return $rs;
    }

    public function getFlowLogic(Request $request){
        $logic = DocFlowLogic::where('doctype_id', $request->doctype_id)->where('flow_prev', $request->flow_prev)
                    ->select('id','doctype_id','flow_prev','flow_next','flow_desc')->get(); 
        return response()->json([
            'status' => $logic->isNotEmpty() ? true : false,
            'data' => $logic,
            'message' => 'Data berhasil di dapat'
        ]);
    }
}
