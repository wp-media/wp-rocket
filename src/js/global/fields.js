var $ = jQuery;
$(document).ready(function(){


    /***
    * Check parent / show children
    ***/

    var $fieldParent = $('.wpr-isParent input[type=checkbox]'),
        $fieldsChildren = $('.wpr-field--children')
    ;

    $fieldParent.change(function() {
        wprShowChildren($(this));
    }).trigger('change');

    function wprShowChildren(aElem){

        var parentId = aElem.attr('id');
        var $children = $('[data-parent="' + parentId + '"]');

            // Test check for switch
            if(aElem.is(':checked')){
                $children.addClass('wpr-isOpen');
            }
            else{
                $children.removeClass('wpr-isOpen');
            }
    }




    /***
    * Warning fields
    ***/

    var $warningParent = $('.wpr-field--parent');
    var $warningParentInput = $('.wpr-field--parent input[type=checkbox]');

    // If already checked
    $warningParentInput.each(function(){
        wprShowChildren($(this));
    });

    $warningParent.change(function() {
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
            $thisCheckbox.attr('checked', false);
            aElem.trigger('change');


            var $warningButton = $warningField.find('.wpr-button');

            // Validate the warning
            $warningButton.click(function(){
                $thisCheckbox.attr('checked', true);
                $warningField.removeClass('wpr-isOpen');
                $children.addClass('wpr-isOpen');

                // If next elem = disabled
                if($nextWarning.length > 0){
                    $nextFields.removeClass('wpr-isDisabled');
                    $nextFields.find('input').attr('disabled', false);
                }

                return false;
            });
        }
        else{
            $nextFields.addClass('wpr-isDisabled');
            $nextFields.find('input').attr('disabled', true);
            $nextFields.find('input[type=checkbox]').attr('checked', false);
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
