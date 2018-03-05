document.addEventListener( 'DOMContentLoaded', function () {


    /*
    ** Warning fields
    */

    var $warning = document.querySelectorAll('.wpr-warningContainer');
    var $warningCheckbox = [];

    for (var i = 0; i < $warning.length; i++) {
        // Input checkbox
        $warningCheckbox[i] = $warning[i].querySelector('input[type=checkbox]');
        wprShowWarning(i);
    }

    function wprShowWarning(i){
        $warningCheckbox[i].onchange = function(){
             var $warningField = $warning[i].querySelector('.wpr-fieldWarning');

             // Check warning parent
             if(this.checked){
                 $warningField.classList.add('wpr-isOpen');
                 this.checked = false;

                 var $warningButton = $warningField.querySelector('.wpr-button');

                 // Validate the warning
                 $warningButton.onclick = function(){
                    $warningCheckbox[i].checked = true;
                    $warningField.classList.remove('wpr-isOpen');
                    return false;
                 }
             }
         };
    }




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
