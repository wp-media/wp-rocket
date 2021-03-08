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

        if ( ! $parent.is( ':checked' ) ) {
            // This field's parent is not checked: don't display the field then.
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


});
