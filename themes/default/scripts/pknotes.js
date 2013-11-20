(function($){

	prepHTML = function(str){
		return str.replace("&nbsp;",'<pknbsp>');
		//return "<![CDATA[" + str + "]]>";
	}
	unprepHTML = function(str){
		return str.replace('<pknbsp>',"&nbsp;");
		//return str.substring( 9, str.length - 3);
	}
	$.fn.pkFilterNotes = function(term){
		$(this).children().fadeTo(0,.3);
		$(this).children().filter(function(){
			var contains = function(str1, str2){
				if (str1.toLowerCase().indexOf(str2.toLowerCase()) != -1){
					return true;
				}
				return false;
			};
			var result = contains($(this).children('a').text(),term);
			var result2 = contains($(this).attr('title'),term);
			return result || result2;
		}).fadeTo(0,1);
	}
	$.fn.pkAddNote = function(title,text, plaintext){
		var newnote = $("<li><a>"+title+"</a><input type='button' value='del'/> \
						<input type='button' value='edit'/></li>");
		newnote.data('notetext', text);
		newnote.attr('title',plaintext);
		newnote.appendTo(this);

				//make buttons active
		newnote.children('input[type=button][value=edit]').click(make_current);
		newnote.children('input[type=button][value=del]').click(delete_note);
		return this;
	}
	$.fn.pkClearOpenNotes = function(){
		$('#pknote_editing').removeAttr('id');
		$("#notesArea input[type=button][value=Save]").prop('disabled',true);
	}
	$.fn.pkSaveNote = function(title,text, plaintext){
		$('#pknote_editing a').text(title);
		$('#pknote_editing').data('notetext', text);
		$('#pknote_editing').attr('title',plaintext);
		$('#pknote_editing').removeAttr('id');
		$("#notesArea input[type=button][value=Save]").prop('disabled',true);
	}
	$.fn.pkLoadNotes = function (options){
		var settings = $.extend({
			tmp : "nothing"
		}, options);

		make_current = function(){
			var editingstring = 'pknote_editing';

			$('#'+editingstring).removeAttr('id');
			$(this).parent().attr('id',editingstring);

			$("#notesArea input[type=text]").val($('#'+editingstring + ' a').text());
			tinyMCE.activeEditor.setContent($('#'+editingstring).data('notetext'));
			$("#notesArea input[type=button][value=Save]").prop('disabled',false);
		};

		delete_note = function(){
			$(this).parent().remove();
		}

		ulist = this;

		$.ajax({
			url: "plugins/pknote_service/note_storage.php",
			type: "GET",
			data: {method: 'getNotes', user : 'paarth'}
		}).done(function(result){
			console.log("result: "+result);
			var wrapper = JSON.parse( result );
			var notelist = wrapper.notes;
			notelist.forEach(function(v){
				var newnote = $("<li><a>"+v.title+"</a><input type='button' value='del'/> \
					<input type='button' value='edit'/></li>");
				
				console.log("Loading text: "+v.text);
				console.log("Which turns into "+unprepHTML(v.text));

				newnote.data('notetext',unprepHTML(v.text));
				newnote.attr('title', $(unprepHTML(v.text)).text());
				newnote.appendTo(ulist);
				/* this is window, for some reason*/

				//make buttons active
				newnote.children('input[type=button][value=edit]').click(make_current);
				newnote.children('input[type=button][value=del]').click(delete_note);
			});
		});

		return this;
	}
	$.fn.pkSaveAllNotes = function(){
		var nlist = [];
		$(this).children().each(function(){
			nlist.push({title: $(this).children('a').text(), text: $(this).data('notetext')});
		});
		console.log("saving nlist: ");
		console.log(nlist);
		
		var jsobj = {notes: nlist};
		console.log(JSON.stringify(jsobj));
		jsstr = prepHTML(JSON.stringify(jsobj));
		console.log(jsstr);
		$.ajax({
			url: "plugins/pknote_service/note_storage.php",
			type: "POST",
			data: {method: 'saveNotes', user : 'paarth', notes: jsstr}
		});
	}
}(jQuery));