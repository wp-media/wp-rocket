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
	$('.has-parent').each( function() {
		var input  = $(this),
			parent = $('#'+$(this).data('parent'));
		
		parent.change( function() {
			if( parent.is(':checked') ) {
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
		if( sup_html5st ) {
    		try {
                sessionStorage.setItem( 'rocket_tab', tab );
            } catch( e ) {}
        }
		$('#rockettabs .rkt-tab').hide();
		$('h2.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
		$('h2.nav-tab-wrapper a[href="'+tab+'"]').addClass('nav-tab-active');
		$(tab).show();
	} );
	if( $('#rockettabs .rkt-tab:visible').length == 0 ){
		$('h2.nav-tab-wrapper a:first').addClass('nav-tab-active');
		$('#tab_apikey').show();
		$('#tab_basic').show();
		if( sup_html5st ) {
            try {
                sessionStorage.setItem( 'rocket_tab', null );
            } catch( e ) {}
        }
	}

	// Sweet Alert for CSS & JS minification
	$( '#minify_css, #minify_js' ).click(function() {
		obj = $(this);
		if ( obj.is( ':checked' ) ) {
			swal({
				title: sawpr.warningTitle,
				html: sawpr.minifyText,
				type: "warning",
				showCancelButton: true,
				confirmButtonColor: "#A5DC86",
				confirmButtonText: sawpr.confirmButtonText,
				cancelButtonText: sawpr.cancelButtonText,
			}).then( function() {
			}, function(dismiss){
				if ( dismiss === 'cancel' ) {
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
				html: sawpr.cloudflareText,
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
				title: sawpr.warningSupportTitle,
				html: sawpr.warningSupportText,
				type: "warning"
			});
		}
		
		if ( validation.is( ':checked' ) && ( summary == '' || description == '' ) ) {
			swal({
				title: sawpr.requiredTitle,
				type: "warning",
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
						confirmButtonText = 'OK';
						confirmButtonColor = '#3085d6';
						type  = "success";

						// Reset the values
						$('#support_summary, #support_description, #support_documentation_validation').val('');
					}

					swal({
						title: title,
						html: text,
						type: type,
						confirmButtonText: confirmButtonText,
						confirmButtonColor: confirmButtonColor,
					}).then(
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
	
	$('#support_summary').parents('fieldset').append( '<div id="support_searchbox" class="hidden"><p><strong>These articles should help you resolving your issue (EN):</strong></p><div id="support_searchbox-suggestions"><ul></ul></div></div>' );
	
    // Live Search Cached Results
    last_search_results = new Array();
    
	 //Listen for the event
	$( "#support_summary" ).on( "keyup", function(e) {
		// Set Search String
		var query_value = $(this).val();
		// Set Timeout
		clearTimeout($.data(this, 'timer'));

		if ( query_value.length < 3 ) {
			$("#support_searchbox").fadeOut();
			$(this).parents('fieldset').attr( 'data-loading', "false" );
			return;
		}

		if ( last_search_results[ query_value ] != undefined ) {
			$(this).parents('fieldset').attr( 'data-loading', "false" );
			$("#support_searchbox-suggestions ul").html(last_search_results[ query_value ]);
			$("#support_searchbox").fadeIn();
			return;
		}
		// Do Search
		$(this).parents('fieldset').attr( 'data-loading', "true" );
		$(this).data('timer', setTimeout(search, 200));
	});
    
    // Live Search
    // On Search Submit and Get Results
    function search() {
        var query_value = $('#support_summary').val();
        if( query_value !== '' ) {
            $.ajax({
                type: "POST",
                url: ajaxurl,
                data: {
	                action : 'rocket_helpscout_live_search',
	                query  : query_value
	            },
                success: function(html) {
	                html = JSON.parse(html);
                    if ( html ) {
	                	last_search_results[ query_value ] = html;
	                	$("#support_searchbox-suggestions ul").html(html);
						$("#support_searchbox").fadeIn();
					}
                    $('#support_summary').parents('fieldset').attr( 'data-loading', "false" );
                }
            });
        }
        return false;
    }
} );