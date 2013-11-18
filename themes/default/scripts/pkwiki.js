/*
author:		Paarth Chadha
plugin: 	pkwiki.js
dependencies:
	* jquery
	* highlight.js 
*/

(function($){

	/* Can add custom entires by including a new item.
	Example: 
		mobile: "http://en.m.wikipedia.org/wiki/"

		to urls and 

		mobile: ""

		to urlback. 

	*/
	var urls = {
		local : "/",
		lib : "http://library.kiwix.org/wikipedia_en_wp1/A/",
		main : "http://en.wikipedia.org/wiki/"
	};
	var urlback = {
		local : ".html",
		lib : ".html",
		main : ""
	};

	$.fn.pkwiki = function (options){
		var settings = $.extend({
			keywords: "",
			loc: "lib",
			display: function(){
				$('a[href=#pane_research]').click();
			}
		}, options);

		return this.each( function(){
			// do for each
			console.log("pkWiki keywords: "+settings.keywords);

			$(this).highlight(settings.keywords.split(','),{element: 'a', className: 'pkwikilink'});

			$('.pkwikilink').live('click',function(e){
				$('#pane_wiki').attr('src',urls[settings.loc] + $(this).text() + urlback[settings.loc]);
				settings.display();

				/* This should probably be handled via a higher order function */
				$.ajax({
					url: "../../plugins/pkwiki_service/toXML.php",
					type: "GET", 
					data: {user : 'default', keyword: $(this).text()}
				}).done(function(msg){
					console.log('Sent to XML: ' + msg);
				});
			});
		});
	}
}(jQuery));
