var $ = jQuery;
$(document).ready(function(){


    /***
    * Check parent / show children
    ***/

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




    /***
    * Warning fields
    ***/

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




    /***
    * Multiple field (for CNAME)
    ***/

    var $buttonMulti = $('.wpr-button--addMulti');
    var $buttonClose = $('.wpr-multiple-close');
    var $multipleListes = $('.wpr-multiple-list');

    $multipleListes.each(function(){
        if($(this).find('.wpr-multiple-line').length > 0){
            $(this).css('display','block');
        }
    });

    $buttonMulti.click(function() {
        wprAddMulti($(this));
        return false;
    });

    $buttonClose.click(function() {
        wprRemoveMulti($(this));
        return false;
    });

    // Add
    function wprAddMulti(aElem){
        var $parent = aElem.parent(),
            $fieldText = $parent.find('input[type=text]'),
            $multipleListe = $parent.parents('.wpr-field').find('.wpr-multiple-list')
        ;

        if($fieldText.val().length > 0){
            $parent.clone().appendTo($parent.parent()).addClass('wpr-isHidden');
            $multipleListe.append('<li class="wpr-multiple-line">' +
                                    '<button class="wpr-multiple-close wpr-icon-close"></button>' +
                                    '<span>'+ $fieldText.val() +'</span>' +
                                    '</li>');
            $multipleListe.css('display','block');
            var $newLine = $multipleListe.find('.wpr-multiple-line:last-child');

            TweenLite.fromTo($newLine, 0.5, {x:20, autoAlpha:0}, {x: 0, autoAlpha:1, ease:Power4.easeOut});

            $fieldText.val('');
            $fieldText.focus();
        }
        else{
            $fieldText.addClass('wpr-isError');

            setTimeout(function() {
                $fieldText.removeClass('wpr-isError');
                $fieldText.focus();
            }, 500);
        }

        $buttonClose = $('.wpr-multiple-close');
        $buttonClose.click(function() {
            wprRemoveMulti($(this));
            return false;
        });
    }


    // Remove
    function wprRemoveMulti(aElem){
        var $parent = aElem.parent(),
            index = $parent.index('.wpr-multiple-list li'),
            $thisField = $parent.parents('.wpr-field').find('.wpr-isHidden')[index],
            $thisList = $parent.parent(),
            $thisLine = $thisList.find('.wpr-multiple-line')[index]
        ;

        TweenLite.to($thisLine, 0.5, {x: 20, autoAlpha:0, ease:Power4.easeOut, onComplete:function(){
            $thisLine.remove();
            $thisField.remove();

            if($thisList.find('.wpr-multiple-line').length < 1){
                $thisList.css('display','none');
            }
        }});

    }

});
