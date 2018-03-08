var $ = jQuery;
$(document).ready(function(){


    /*
    ** Check parent / show children
    */

    var $fieldParent = $('.wpr-isParent');
    $fieldParent.change(function() {
        wprShowChildren($(this));
    }).trigger('change');

    function wprShowChildren(aElem){
        // Get all children
        var $children = aElem.nextUntil(':not(.wpr-field--children)','.wpr-field--children');

        // Test check for switch
        if(aElem.find('input[type=checkbox]').is(':checked')){
            $children.addClass('wpr-isOpen');
        }
        else{
            $children.removeClass('wpr-isOpen');
            $children.each(function(){
                $(this).find('input[type=checkbox]').attr('checked', false);
            });
        }
    }




    /*
    ** Warning fields
    */

    var $warningParent = $('.wpr-field--parent');

    $warningParent.change(function() {
        wprShowWarning($(this));
    });

    function wprShowWarning(aElem){
        var $warningField = aElem.next('.wpr-fieldWarning'),
            $thisCheckbox = aElem.find('input[type=checkbox]'),
            $nextWarning = aElem.parent().next('.wpr-warningContainer'),
            $nextFields = $nextWarning.find('.wpr-field')
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
        }
    }


});
