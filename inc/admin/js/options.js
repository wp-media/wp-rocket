jQuery( document ).ready( function($){
	// Fancybox
	$(".fancybox").fancybox({'type' : 'iframe'});

	// Deferred JS
	function rocket_deferred_rename()
	{
		$('#rkt-drop-deferred .rkt-module-drag').each( function(i){
			var $item_t_input = $(this).find( 'input[type=text]' );
			var $item_c_input = $(this).find( 'input[type=checkbox]' );
			$($item_t_input).attr( 'name', 'wp_rocket_settings[deferred_js_files]['+i+']' );
			$($item_c_input).attr( 'name', 'wp_rocket_settings[deferred_js_wait]['+i+']' );
		});
	}

	// Minify JS in footer
	function rocket_minify_js_rename() {
		$('#rkt-drop-minify_js_in_footer .rkt-module-drag').each( function(i){
			var $item_t_input = $(this).find( 'input[type=text]' );
			$($item_t_input).attr( 'name', 'wp_rocket_settings[minify_js_in_footer]['+i+']' );
		});
	}

	$('.rkt-module-drop').sortable({
		update : function() {
			if ( $(this).attr('id') == 'rkt-drop-deferred' ) {
				rocket_deferred_rename();
			}

			if ( $(this).attr('id') == 'rkt-drop-minify_js_in_footer' ) {
				rocket_minify_js_rename();
			}
		},
		axis: "y",
		items: ".rkt-module-drag",
		containment: "parent",
		cursor: "move",
		handle: ".rkt-module-move",
		forcePlaceholderSize: true,
		dropOnEmpty: false,
		placeholder: 'sortable-placeholder',
		tolerance: 'pointer',
		revert: true,
	});

	// Remove input
	$('.rkt-module-remove').css('cursor','pointer').live('click', function(e){
		e.preventDefault();
		$(this).parent().css('background-color','red' ).slideUp( 'slow' , function(){$(this).remove(); } );
	} );

	// CNAMES
	$('.rkt-module-clone').on('click', function(e)
	{
		var moduleID = $(this).parent().siblings('.rkt-module').attr('id');

		e.preventDefault();
		$($('#' + moduleID ).siblings('.rkt-module-model:last')[0].innerHTML).appendTo('#' + moduleID);

		if( moduleID == '' ) {
			rocket_deferred_rename();
		}

	});

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
	$( 'h2.nav-tab-wrapper .nav-tab' ).on( 'click', function(e){
		e.preventDefault();
		tab = $(this).attr( 'href' );
		if( sup_html5st ) sessionStorage.setItem( 'rocket_tab', tab );
		$('#rockettabs .rkt-tab').hide();
		$('h2.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
		$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
		$(tab).show();
	} );
	if( $('#rockettabs .rkt-tab:visible').length == 0 ){
		$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
		$('#tab_apikey').show();
		$('#tab_basic').show();
		if( sup_html5st ) sessionStorage.setItem( 'rocket_tab', null );
	}

	// Sweet Alert for CSS & JS minification
	$( '#minify_css, #minify_js' ).click(function() {
		obj = $(this);
		if ( obj.is( ':checked' ) ) {
			swal(
			{
				title: sawpr.warning_title,
				text: sawpr.minify_text,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#A5DC86",
				confirmButtonText: sawpr.confirmButtonTextBis,
				cancelButtonText: sawpr.cancelButtonText,
				closeOnConfirm: true,
				closeOnCancel: true
			},
			function(isConfirm){
				if (!isConfirm) {
					obj.attr('checked', false);
				}
			});
		}
	});
	
	// Sweet Alert for CloudFlare activation
	$( '#do_cloudflare' ).click(function() {
		if ( $(this).is( ':checked' ) ) {
			swal({   
				title: sawpr.cloudflare_title,   
				text: sawpr.cloudflare_text,   
				timer: 5000 
			});
		}
	});
} );