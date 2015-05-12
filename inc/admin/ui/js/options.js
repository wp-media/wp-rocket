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
	
	// Inputs with parent
	$('input[data-parent]').each( function() {
		var input  = $(this),
			parent = $('#'+$(this).data('parent'));
		
		parent.change( function() {
			if( $(this).is(':checked') ) {
				input.parents('fieldset').show(200);
			} else {
				input.parents('fieldset').hide(200);
			}
		});
		
		if( ! parent.is(':checked') ) {
			$(this).parents('fieldset').hide();
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
				title: sawpr.warningTitle,
				text: sawpr.minifyText,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#A5DC86",
				confirmButtonText: sawpr.confirmButtonTextBis,
				cancelButtonText: sawpr.cancelButtonText,
				closeOnConfirm: true,
				closeOnCancel: true,
				html: true
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
				title: sawpr.cloudflareTitle,
				text: sawpr.cloudflareText,
				timer: 5000
			});
		}
	});

	// Support form
	$( '#submit-support-button' ).click( function(e) {
		e.preventDefault();

		var summary 	= $('#support_summary').val().trim(),
			description = $('#support_description').val().trim(),
			validation  = $('#support_documentation_validation'),
			wpnonce		= $('#_wpnonce').val();

		if ( ! validation.is( ':checked' ) ) {
			swal({
				title : sawpr.warningSupportTitle,
				text  : sawpr.warningSupportText,
				type  : "warning",
				html  : true
			});
		}

		if ( summary != '' && description != '' && validation.is( ':checked' ) ) {

			swal({
				title: sawpr.preloaderTitle,
				showCancelButton: false,
				showConfirmButton: false,
				imageUrl: sawpr.preloaderImg,
			});

			$.post(
				ajaxurl,
				{
					action: 'rocket_new_ticket_support',
					summary: summary,
					description: description,
					_wpnonce: wpnonce,
				},
				function(response) {
					response = JSON.parse(response);
					var title, text, type, confirmButtonText, confirmButtonColor;
					if( response.msg == 'BAD_EMAIL' ) {
						title              = sawpr.badSupportTitle;
						text               = sawpr.badSupportText;
						confirmButtonText  = sawpr.badConfirmButtonText;
						confirmButtonColor = "#f7a933";
						type               = "error";
					}

					if( response.msg == 'BAD_LICENCE' ) {
						title = sawpr.expiredSupportTitle;
						text  = sawpr.expiredSupportText;
						confirmButtonText  = sawpr.expiredConfirmButtonText;
						confirmButtonColor = "#f7a933";
						type  = "warning";
					}
					
					if( response.msg == 'BAD_CONNECTION' ) {
						title = sawpr.badServerConnectionTitle;
						text  = sawpr.badServerConnectionText;
						confirmButtonText  = sawpr.badServerConnectionConfirmButtonText;
						confirmButtonColor = "#f7a933";
						type  = "error";
					}
					
					if( response.msg == 'SUCCESS' ) {
						title = sawpr.successSupportTitle;
						text  = sawpr.successSupportText;
						type  = "success";

						// Reset the values
						$('#support_summary, #support_description, #support_documentation_validation').val('');
					}

					swal({
						title : title,
						text  : text,
						type  : type,
						confirmButtonText : confirmButtonText,
						confirmButtonColor : confirmButtonColor,
						html  : true
					},
					function() {
						if( response.msg == 'BAD_EMAIL' ) {
							window.open(response.order_url);
						}

						if( response.msg == 'BAD_LICENCE' ) {
							window.open(response.renew_url);
						}
						
						if( response.msg == 'BAD_CONNECTION' ) {
							window.open('http://wp-rocket.me/support/');
						}
					});
				}
			);
		}
	});
} );