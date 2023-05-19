<!DOCTYPE html>
<html lang="en">
<head>
<title>ACE in Action</title>
<style type="text/css" media="screen">
    .toolbar {
        height: 5vh;
    } 
    .ace_editor {
        position: relative; 
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 90vh;
    }
    .save-button{
        font-size: 18px;
    }
</style>
</head>
<body>

    <input type="hidden" name="trans_id" id="trans_id" value="{{$trans_id}}" />
    <input type="hidden" name="gtype" id="gtype" value="{{$getdatatype}}" />

<script src="{{asset('assets/src-noconflict/ace.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('assets/src-noconflict/theme-twilight.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('assets/src-noconflict/mode-mysql.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="{{asset('assets/src-noconflict/ext-language_tools.js')}}" type="text/javascript" charset="utf-8"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js" type="text/javascript"></script>
<script>
    var buildDom = ace.require("ace/lib/dom").buildDom;
    var editor = ace.edit();
    var transID = $('#trans_id').val();
    var record;
    var gType = $('#gtype').val();

    $(document).ready(function(){
        jQuery.ajax({
            type: "GET",
            url: "{{ route('data.editor') }}",
            data : {transid:transID, gtype:gType},
            dataType: 'json',
            cache: false,
            success: function(response)
            {
				editor.setValue(response["msg"].query_update);
            }
        });
    });
    
    editor.setTheme("ace/theme/twilight");

    editor.setOptions({
        autoScrollEditorIntoView: true,
        copyWithEmptySelection: true,
        fontSize : 14,
        enableBasicAutocompletion : true,
        enableSnippets: true,
    });

    var MYSQLMode = ace.require("ace/mode/mysql").Mode;
    editor.session.setMode(new MYSQLMode());

    var refs = {};
    function updateToolbar() {
        refs.saveButton.disabled = editor.session.getUndoManager().isClean();
    }
    editor.on("input", updateToolbar);
    function save() {
        localStorage.savedValue = editor.getValue(); 
        record = editor.getValue();
        editor.session.getUndoManager().markClean();
        updateToolbar();
        updateRecord(transID,record);
    }

    function updateRecord(id,record)
    {
        jQuery.ajax({
            type: "POST",
            url: "{{ route('update.editor') }}",
            data: {transid:id, recordText:record, gtype:gType},
            cache: false,
            success: function(response)
            {
                //alert(response.message);
				var tId;
				$("#messageBox").text('Success');
				$("#messageBox").hide().slideDown();
				clearTimeout(tId);
				tId=setTimeout(function(){
				  $("#messageBox").hide();        
				}, 3000);
            }
        });
    }

    editor.commands.addCommand({
        name: "save",
        exec: save,
        bindKey: { win: "ctrl-s", mac: "cmd-s" }
    });

    buildDom(["div", { class: "toolbar"},
        ["button", {
            class: "save-button",
            ref: "saveButton",
            onclick: save
        }, "Save"],
    ], document.body, refs);
	buildDom(["div", { id: "messageBox", style:"color:gray;" }], document.body, refs);
    document.body.appendChild(editor.container)
    window.editor = editor;
</script>
</body>
</html>