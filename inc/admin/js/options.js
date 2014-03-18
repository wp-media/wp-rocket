jQuery( document ).ready( function($){
	
	// Fancybox
	$(".fancybox").fancybox({'type' : 'iframe'});

	// Deferred JS
	function rocket_rename()
	{
		$('#rkt-drop-deferred .rkt-drag-deferred').each( function(i){
			var $item_t_input = $(this).find( 'input[type=text]' );
			var $item_c_input = $(this).find( 'input[type=checkbox]' );
			$($item_t_input).attr( 'name', 'wp_rocket_settings[deferred_js_files]['+i+']' );
			$($item_c_input).attr( 'name', 'wp_rocket_settings[deferred_js_wait]['+i+']' );
		});
	}
	$('#rkt-drop-deferred').sortable({
		update : function(){ rocket_rename(); },
		axis: "y",
		items: ".rkt-drag-deferred",
		containment: "parent",
		cursor: "move",
		handle: ".rkt-move-deferred",
		forcePlaceholderSize: true,
		dropOnEmpty: false,
		placeholder: 'sortable-placeholder',
		tolerance: 'pointer',
		revert: true,
	});
	$('#rkt-clone-deferred').on('click', function(e){
		e.preventDefault();
		if( $('#rkt-drop-deferred .rkt-drag-deferred:last input[type=text]').val()=='' )
			return;
		var $item = $('.rkt-model-deferred:last').clone().appendTo('#rkt-drop-deferred').removeClass('rkt-model-deferred').show();
		rocket_rename();
	} );
	$('.rkt-delete-deferred').css('cursor','pointer').on('click', function(e){
		e.preventDefault();
		$(this).parent().css('background-color','red' ).slideUp( 'slow' , function(){$(this).remove(); } );
	} );
	
	// CNAMES
	$('#rkt-clone-cname').on('click', function(e)
	{
		e.preventDefault();
		if( $('#rkt-cnames .rkt-cname:last input[type=text]').val()=='' )
			return;
		var $item = $('.rkt-model-cname:last').clone().appendTo('#rkt-cnames').removeClass('rkt-model-cname').show();
		rocket_rename();
	} );
	$('.rkt-delete-cname').css('cursor','pointer').on('click', function(e)
	{
		e.preventDefault();
		$(this).parent().css('background-color','red' ).slideUp( 'slow' , function(){$(this).remove(); } );
	} );
	
	// Tabs
	$('#rockettabs').css({padding: '5px', border: '1px solid #ccc', borderTop: '0px'});
	$('.nav-tab-wrapper a').css({outline: '0px'});
	$('#rockettabs .rkt-tab').hide();
	$('#rockettabs h3').hide();
	var sup_html5st = 'sessionStorage' in window && window['sessionStorage'] !== undefined;
	if( sup_html5st ) {
		var tab = unescape( sessionStorage.getItem( 'rocket_tab' ) );
		if( tab!='null' && tab!=null && tab!=undefined && $('h2.nav-tab-wrapper a[href="'+tab+'"]').length==1 ) {
			$('#rockettabs .nav-tab').hide();
			$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
			$(tab).show();
		}else{
			$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
			if( $('#tab_basic').length==1 )
				$('#tab_basic').show();
		}
	}
	$('h2.nav-tab-wrapper .nav-tab').on( 'click', function(e){
		e.preventDefault();
		tab = $(this).attr( 'href' );
		if( sup_html5st ) sessionStorage.setItem( 'rocket_tab', tab );
		$('#rockettabs .rkt-tab').hide();
		$('h2.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
		$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
		$(tab).show();
	} );
	if( $('#rockettabs .rkt-tab:visible').length==0 ){
		$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
		$('#tab_apikey').show();
		$('#tab_basic').show();
		if( sup_html5st ) sessionStorage.setItem( 'rocket_tab', null );
	}
	
} );