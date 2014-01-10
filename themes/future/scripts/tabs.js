	$(function() {                                   // <== shorter form of doc ready
	    $('#tabs > div').hide();
	    $('#tabs div:first').fadeIn('slow');
	    $('#tabs ul li:first').addClass('active');
	    $('#tabs ul li a').click(function(){
	        $('#tabs ul li.active').removeClass('active');  // <== Only what you need
	        $(this).parent().addClass('active');
	        var selectedTab=$(this).attr('href');
	        $('#tabs > div').fadeOut('fast', function() {       // <== Use a callback
	            $(selectedTab).delay(500).fadeIn('fast');          // <== add a delay
	        });        
	        return false;
	    });
	});