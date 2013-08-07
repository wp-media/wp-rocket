function rocket_setCookie(c_name,value,exdays)
{
	var exdate=new Date();
	exdate.setDate(exdate.getDate() + exdays);
	var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
	document.cookie=c_name + "=" + c_value;
}

function rocket_getCookie(c_name) {
    var c_value = document.cookie;
    var c_start = c_value.indexOf(" " + c_name + "=");
    if (c_start == -1) {
        c_start = c_value.indexOf(c_name + "=");
    }
    if (c_start == -1) {
        c_value = null;
    } else {
        c_start = c_value.indexOf("=", c_start) + 1;
        var c_end = c_value.indexOf(";", c_start);
        if (c_end == -1) {
            c_end = c_value.length;
        }
        c_value = unescape(c_value.substring(c_start, c_end));
    }
    return c_value;
}

jQuery( document ).ready( function($){
	
	// Fancybox
	$(".fancybox").fancybox({'type' : 'iframe'});
	
	// Deffered JS
	function rocket_rename()
	{
		$('#rktdrop .rktdrag').each( function(i){
			var $item_t_input = $(this).find( 'input[type=text]' );
			var $item_c_input = $(this).find( 'input[type=checkbox]' );
			$($item_t_input).attr( 'name', '<?php echo WP_ROCKET_SLUG; ?>[deferred_js_files]['+i+']' );
			$($item_c_input).attr( 'name', '<?php echo WP_ROCKET_SLUG; ?>[deferred_js_wait]['+i+']' );
		});
	}
	$('#rktdrop').sortable({
		update : function(){ rocket_rename(); },
		axis: "y",
		items: ".rktdrag",
		containment: "parent",
		cursor: "move",
		handle: ".rktmove",
		forcePlaceholderSize: true,
		dropOnEmpty: false,
		placeholder: 'sortable-placeholder',
		tolerance: 'pointer',
		revert: true,
	});
	$('#rktclone').on('click', function(e){
		e.preventDefault();
		if( $('#rktdrop .rktdrag:last input[type=text]').val()=='' )
			return;
		var $item = $('.rktmodel:last').clone().appendTo('#rktdrop').removeClass('rktmodel').show();
		rocket_rename();
	} );
	$('.rktdelete').css('cursor','pointer').on('click', function(e){
		e.preventDefault();
		$(this).parent().css('background-color','red' ).slideUp( 'slow' , function(){$(this).remove(); } );
	} );
	
	// Tabs
	$('#rockettabs').css({padding: '5px', border: '1px solid #ccc', borderTop: '0px'});
	$('.nav-tab-wrapper a').css({outline: '0px'});
	$('#rockettabs .rkt-tab').hide();
	$('#rockettabs h3').hide();
	var tab = unescape( rocket_getCookie( 'rocket_tab' ) );
	if( tab!='' ) {
		$('#rockettabs .nav-tab').hide();
		$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
		$(tab).show();
	}else{
		$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
		if( $('#tab_basic').length==1 )
			$('#tab_basic').show();
	}
	$('h2.nav-tab-wrapper .nav-tab').on( 'click', function(e){
		e.preventDefault();
		tab = $(this).attr( 'href' );
		rocket_setCookie( 'rocket_tab', tab, 365 );
		$('#rockettabs .rkt-tab').hide();
		$('h2.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
		$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
		$(tab).show();
	} );
	if( $('#rockettabs .rkt-tab:visible').length==0 ){
		$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
		$('#tab_apikey').show();
		rocket_setCookie( 'rocket_tab', '', -1 );
	}
	
} );