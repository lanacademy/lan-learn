    $(function(){
        tinymce.init({
            selector: "textarea#editor_side",
            menubar: false,
            statusbar: false,
            encoding: "xml",
            entity_encoding: "named",
            plugins: [
                "advlist autolink lists link image charmap print preview anchor",
                "searchreplace visualblocks code fullscreen",
                "insertdatetime media table contextmenu paste"
            ],
            toolbar: "false";
        });

        var notelist = $('#notesList ul');

        check_populated = function(){
            if($('#notesArea input[type=text]').val().length == 0){
                return false;
                if( $(tinymce.activeEditor.getContent()).text() == ''){
                    return false;
                }
            }
            return true;
        };
        notelist.pkLoadNotes();
        $('#notesArea input[type=button][value=Discard]').click(function(){
            $('#notesArea input[type=text]').val('');
            tinyMCE.activeEditor.setContent('');
            $(notelist).pkClearOpenNotes();
        });
        $('#notesArea input[type=button][value=Save]').click(function(){
            if(!check_populated()){
                alert("Note not filled out");
                return;
            }
            if($('#pknote_editing').length == 0){
                $("#notesArea input[type=button][value='Save as New']").click();
                return;
            }
            notelist.pkSaveNote($('#notesArea input[type=text]').val(),tinyMCE.activeEditor.getContent(),tinyMCE.activeEditor.getContent({format: 'text'}));
            $('#notesArea input[type=text]').val('');
            tinyMCE.activeEditor.setContent('');
            notelist.pkSaveAllNotes();
        });
        $("#notesArea input[type=button][value='Save as New']").click(function(){
            if(!check_populated()){
                alert("Note not filled out");
                return;
            }
            notelist.pkAddNote($('#notesArea input[type=text]').val(), tinyMCE.activeEditor.getContent(), tinyMCE.activeEditor.getContent({format: 'text'}));
            notelist.pkSaveAllNotes();
            $('#notesArea input[type=button][value=Discard]').click();
        });
        $("#notesList input[type=text]").keydown(function(){
            notelist.pkFilterNotes($(this).val());
        })
        $("#notesList input[type=text]").keyup(function(){
            notelist.pkFilterNotes($(this).val());
        })
        $("#notesArea input[type=button][value=Save]").prop('disabled',true);
    });