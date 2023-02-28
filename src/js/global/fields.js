var $ = jQuery;
$(document).ready(function(){


    /***
    * Check parent / show children
    ***/

	function wprShowChildren(aElem){
		var parentId, $children;

		aElem     = $( aElem );
		parentId  = aElem.attr('id');
		$children = $('[data-parent="' + parentId + '"]');

		// Test check for switch
		if(aElem.is(':checked')){
			$children.addClass('wpr-isOpen');

			$children.each(function() {
				if ( $(this).find('input[type=checkbox]').is(':checked')) {
					var id = $(this).find('input[type=checkbox]').attr('id');

					$('[data-parent="' + id + '"]').addClass('wpr-isOpen');
				}
			});
		}
		else{
			$children.removeClass('wpr-isOpen');

			$children.each(function() {
				var id = $(this).find('input[type=checkbox]').attr('id');

				$('[data-parent="' + id + '"]').removeClass('wpr-isOpen');
			});
		}
	}

    /**
     * Tell if the given child field has an active parent field.
     *
     * @param  object $field A jQuery object of a ".wpr-field" field.
     * @return bool|null
     */
    function wprIsParentActive( $field ) {
        var $parent;

        if ( ! $field.length ) {
            // ¯\_(ツ)_/¯
            return null;
        }

        $parent = $field.data( 'parent' );

        if ( typeof $parent !== 'string' ) {
            // This field has no parent field: then we can display it.
            return true;
        }

        $parent = $parent.replace( /^\s+|\s+$/g, '' );

        if ( '' === $parent ) {
            // This field has no parent field: then we can display it.
            return true;
        }

        $parent = $( '#' + $parent );

        if ( ! $parent.length ) {
            // This field's parent is missing: let's consider it's not active then.
            return false;
        }

        if ( ! $parent.is( ':checked' ) && $parent.is('input')) {
            // This field's parent is checkbox and not checked: don't display the field then.
            return false;
        }

		if ( !$parent.hasClass('radio-active') && $parent.is('button')) {
			// This field's parent button and is not active: don't display the field then.
			return false;
		}
        // Go recursive to the last parent.
        return wprIsParentActive( $parent.closest( '.wpr-field' ) );
    }

    // Display/Hide childern fields on checkbox change.
    $( '.wpr-isParent input[type=checkbox]' ).on('change', function() {
        wprShowChildren($(this));
    });

    // On page load, display the active fields.
    $( '.wpr-field--children' ).each( function() {
        var $field = $( this );

        if ( wprIsParentActive( $field ) ) {
            $field.addClass( 'wpr-isOpen' );
        }
    } );




    /***
    * Warning fields
    ***/

    var $warningParent = $('.wpr-field--parent');
    var $warningParentInput = $('.wpr-field--parent input[type=checkbox]');

    // If already checked
    $warningParentInput.each(function(){
        wprShowChildren($(this));
    });

    $warningParent.on('change', function() {
        wprShowWarning($(this));
    });

    function wprShowWarning(aElem){
        var $warningField = aElem.next('.wpr-fieldWarning'),
            $thisCheckbox = aElem.find('input[type=checkbox]'),
            $nextWarning = aElem.parent().next('.wpr-warningContainer'),
            $nextFields = $nextWarning.find('.wpr-field'),
            parentId = aElem.find('input[type=checkbox]').attr('id'),
            $children = $('[data-parent="' + parentId + '"]')
        ;

        // Check warning parent
        if($thisCheckbox.is(':checked')){
            $warningField.addClass('wpr-isOpen');
            $thisCheckbox.prop('checked', false);
            aElem.trigger('change');


            var $warningButton = $warningField.find('.wpr-button');

            // Validate the warning
            $warningButton.on('click', function(){
                $thisCheckbox.prop('checked', true);
                $warningField.removeClass('wpr-isOpen');
                $children.addClass('wpr-isOpen');

                // If next elem = disabled
                if($nextWarning.length > 0){
                    $nextFields.removeClass('wpr-isDisabled');
                    $nextFields.find('input').prop('disabled', false);
                }

                return false;
            });
        }
        else{
            $nextFields.addClass('wpr-isDisabled');
            $nextFields.find('input').prop('disabled', true);
            $nextFields.find('input[type=checkbox]').prop('checked', false);
            $children.removeClass('wpr-isOpen');
        }
    }

    /**
     * CNAMES add/remove lines
     */
    $(document).on('click', '.wpr-multiple-close', function(e) {
		e.preventDefault();
		$(this).parent().slideUp( 'slow' , function(){$(this).remove(); } );
	} );

	$('.wpr-button--addMulti').on('click', function(e) {
		e.preventDefault();
        $($('#wpr-cname-model').html()).appendTo('#wpr-cnames-list');
    });

	/***
	 * Wpr Radio button
	 ***/
	var disable_radio_warning = false;

	$(document).on('click', '.wpr-radio-buttons-container button', function(e) {
		e.preventDefault();
		if($(this).hasClass('radio-active')){
			return false;
		}
		var $parent = $(this).parents('.wpr-radio-buttons');
		$parent.find('.wpr-radio-buttons-container button').removeClass('radio-active');
		$parent.find('.wpr-extra-fields-container').removeClass('wpr-isOpen');
		$parent.find('.wpr-fieldWarning').removeClass('wpr-isOpen');
		$(this).addClass('radio-active');
		wprShowRadioWarning($(this));

	} );


	function wprShowRadioWarning($elm){
		disable_radio_warning = false;
		$elm.trigger( "before_show_radio_warning", [ $elm ] );
		if (!$elm.hasClass('has-warning') || disable_radio_warning) {
			wprShowRadioButtonChildren($elm);
			$elm.trigger( "radio_button_selected", [ $elm ] );
			return false;
		}
		var $warningField = $('[data-parent="' + $elm.attr('id') + '"].wpr-fieldWarning');
		$warningField.addClass('wpr-isOpen');
		var $warningButton = $warningField.find('.wpr-button');

		// Validate the warning
		$warningButton.on('click', function(){
			$warningField.removeClass('wpr-isOpen');
			wprShowRadioButtonChildren($elm);
			$elm.trigger( "radio_button_selected", [ $elm ] );
			return false;
		});
	}

	function wprShowRadioButtonChildren($elm) {
		var $parent = $elm.parents('.wpr-radio-buttons');
		var $children = $('.wpr-extra-fields-container[data-parent="' + $elm.attr('id') + '"]');
		$children.addClass('wpr-isOpen');
	}

	/***
	 * Wpr Optimize Css Delivery Field
	 ***/
	var rucssActive = parseInt($('#remove_unused_css').val());

	$( "#optimize_css_delivery_method .wpr-radio-buttons-container button" )
		.on( "radio_button_selected", function( event, $elm ) {
			toggleActiveOptimizeCssDeliveryMethod($elm);
		});

	$("#optimize_css_delivery").on("change", function(){
		if( $(this).is(":not(:checked)") ){
			disableOptimizeCssDelivery();
		}else{
			var default_radio_button_id = '#'+$('#optimize_css_delivery_method').data( 'default' );
			$(default_radio_button_id).trigger('click');
		}
	});

	function toggleActiveOptimizeCssDeliveryMethod($elm) {
		var optimize_method = $elm.data('value');
		if('remove_unused_css' === optimize_method){
			$('#remove_unused_css').val(1);
			$('#async_css').val(0);
		}else{
			$('#remove_unused_css').val(0);
			$('#async_css').val(1);
		}

	}

	function disableOptimizeCssDelivery() {
		$('#remove_unused_css').val(0);
		$('#async_css').val(0);
	}

	$( "#optimize_css_delivery_method .wpr-radio-buttons-container button" )
		.on( "before_show_radio_warning", function( event, $elm ) {
			disable_radio_warning = ('remove_unused_css' === $elm.data('value') && 1 === rucssActive)
		});

	var key = document.getElementById( 'cloudflare_api_key' );
	var zone = document.getElementById( 'cloudflare_zone_id' );

	if ( key.value.length != 0 ) {
		key.value = hideFieldValue( key.value );
	}

	if ( zone.value.length != 0 ) {
		zone.value = hideFieldValue( zone.value );
	}

	function hideFieldValue( str ) {
		const show = 4;

		return '*'.repeat( str.length - show ) + str.slice( -show );
	}
});
