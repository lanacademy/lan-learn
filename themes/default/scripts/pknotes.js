(function($){
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
	$.fn.pkAddNote = function(title,text){
		var newnote = $("<li><a>"+title+"</a><input type='button' value='del'/> \
						<input type='button' value='edit'/></li>");
		newnote.attr('title',$(text).text());
		newnote.appendTo(this);

				//make buttons active
		newnote.children('input[type=button][value=edit]').click(make_current);
		newnote.children('input[type=button][value=del]').click(delete_note);
		return this;
	}
	$.fn.pkClearOpenNotes = function(){
		$('#pknote_editing').removeAttr('id');
	}
	$.fn.pkSaveNote = function(title,text){
		$('#pknote_editing a').text(title);
		$('#pknote_editing').attr('title',$(text).text());
		$('#pknote_editing').removeAttr('id');
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
			tinyMCE.activeEditor.setContent($('#'+editingstring).attr('title'));

		};

		delete_note = function(){
			$(this).parent().remove();
		}

		ulist = this;

		$.ajax({
			url: "../../plugins/pknote_service/note_storage.php",
			type: "GET",
			data: {method: 'getNotes', user : 'paarth'}
		}).done(function(result){
			var wrapper = $.parseJSON( result );
			var notelist = wrapper.notes;
			notelist.forEach(function(v){
				var newnote = $("<li><a>"+v.title+"</a><input type='button' value='del'/> \
					<input type='button' value='edit'/></li>");
				newnote.attr('title',v.text);
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
			nlist.push({title: $(this).children('a').text(), text: $(this).attr('title')});
		});
		var jsobj = {notes: nlist};
		$.ajax({
			url: "../../plugins/pknote_service/note_storage.php",
			type: "GET",
			data: {method: 'saveNotes', user : 'paarth', notes: JSON.stringify(jsobj)}
		});
	}
}(jQuery));