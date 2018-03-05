document.addEventListener( 'DOMContentLoaded', function () {


    /*
    ** Warning fields
    */
    //
    // var $warningsTab = document.querySelectorAll('.wpr-field--parent');
    //
    // for (var i = 0; i < $warningsTab.length; i++) {
    //
    //
    //     // Input checkbox
    //     var $checkbox = $prev.querySelector('input[type=checkbox]');
    //     $checkbox.onchange = function(){
    //         //$warning.classList.add('wpr-isOpen');
    //         return false;
    //     };
    // }




    /*
    ** Disabled fields
    */

    var $disabledFields = document.querySelectorAll('.wpr-isDisabled');
    var $parentInput = [];

    for (var i = 0; i < $disabledFields.length; i++) {
        // Get prev element
        var $prev = $disabledFields[i].parentNode.previousSibling;
        while($prev && $prev.nodeType != 1) {
            $prev = $prev.previousSibling;
        }
        $parentInput[i] = $prev.querySelector('input[type=checkbox]');

        wprShowDisabled(i);
    }

    function wprShowDisabled(i){
        $parentInput[i].onchange = function(){
             if(this.checked){
                 $disabledFields[i].classList.remove('wpr-isDisabled');
                 $disabledFields[i].querySelector('input[type=checkbox]').disabled = false;
             }
             else{
                 $disabledFields[i].classList.add('wpr-isDisabled');
                 $disabledFields[i].querySelector('input[type=checkbox]').disabled = true;
                 $disabledFields[i].querySelector('input[type=checkbox]').checked = false;
             }
         };
    }

});
