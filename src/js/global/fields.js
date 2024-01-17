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

	/**
	 * Masks sensitive information in an input field by replacing all but the last 4 characters with asterisks.
	 *
	 * @param {string} id_selector - The ID of the input field to be masked.
	 * @returns {void} - Modifies the input field value in-place.
	 *
	 * @example
	 * // HTML: <input type="text" id="creditCardInput" value="1234567890123456">
	 * maskField('creditCardInput');
	 * // Result: Updates the input field value to '************3456'.
	 */
	function maskField(proxy_selector, concrete_selector) {
		var concrete = {
			'val': concrete_selector.val(),
			'length': concrete_selector.val().length
		}

		if (concrete.length > 4) {

			var hiddenPart = '\u2022'.repeat(Math.max(0, concrete.length - 4));
			var visiblePart = concrete.val.slice(-4);

			// Combine the hidden and visible parts
			var maskedValue = hiddenPart + visiblePart;

			proxy_selector.val(maskedValue);
		}

		proxy_selector.on('input', function () {
			var proxyValue = proxy_selector.val();

			// Check if the proxy value contains '*' before updating the concrete field
			if (proxyValue.indexOf('\u2022') === -1) {
				concrete_selector.val(proxyValue);
			}
		});
	}

		// Update the concrete field when the proxy is updated.


	maskField($('#cloudflare_api_key_mask'), $('#cloudflare_api_key'));
	maskField($('#cloudflare_zone_id_mask'), $('#cloudflare_zone_id'));

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

	$( ".wpr-multiple-select .wpr-list-header-arrow" ).click(function (e) {
		$(e.target).closest('.wpr-multiple-select .wpr-list').toggleClass('open');
	});

	$('.wpr-multiple-select .wpr-checkbox').click(function (e) {
		const checkbox = $(this).find('input');
		const is_checked = checkbox.attr('checked') !== undefined;
		checkbox.attr('checked', is_checked ? null : 'checked' );
		const sub_checkboxes = $(checkbox).closest('.wpr-list').find('.wpr-list-body input[type="checkbox"]');
		if(checkbox.hasClass('wpr-main-checkbox')) {
			$.map(sub_checkboxes, checkbox => {
				$(checkbox).attr('checked', is_checked ? null : 'checked' );
			});
			return;
		}
		const main_checkbox = $(checkbox).closest('.wpr-list').find('.wpr-main-checkbox');

		const sub_checked =  $.map(sub_checkboxes, checkbox => {
			if($(checkbox).attr('checked') === undefined) {
				return ;
			}
			return checkbox;
		});
		main_checkbox.attr('checked', sub_checked.length === sub_checkboxes.length ? 'checked' : null );
	});

	if ( $( '.wpr-main-checkbox' ).length > 0 ) {
		$('.wpr-main-checkbox').each((checkbox_key, checkbox) => {
			let parent_list = $(checkbox).parents('.wpr-list');
			let not_checked = parent_list.find( '.wpr-list-body input[type=checkbox]:not(:checked)' ).length;
			$(checkbox).attr('checked', not_checked <= 0 ? 'checked' : null );
		});
	}
});
