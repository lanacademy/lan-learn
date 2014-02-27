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
	this.capitalize = function(s){
		return s.trim().charAt(0).toUpperCase() + s.trim().slice(1);
	}
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

			var words = settings.keywords.split(',').map(function(v){return capitalize(v);});
			console.log("pkWiki keywords: " + words);

			$(this).highlight(words,{element: 'a', className: 'pkwikilink'});

			$('.pkwikilink').live('click',function(e){
				$('#pane_wiki').attr('src',urls[settings.loc] + capitalize($(this).text()) + urlback[settings.loc]);
				settings.display();

				/* This should probably be handled via a higher order function */
                $.ajax({
                    url: "../../plugins/pkwiki_service/toXML.php",
                    type: "GET", 
                    /* the user field should be determined in toXML's php, not here */
                    data: {user : 'default', keyword: capitalize($(this).text())}
                }).done(function(msg){
                    console.log('Sent to XML, got:' + msg);
                });
			});
		});
	}
}(jQuery));
