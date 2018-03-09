var $ = jQuery;
$(document).ready(function(){


    /***
    * Modal desactivation plugin
    ***/

    var $wprModal = $('.wpr-Modal'),
        $wprModalOverlay = $('.wpr-Modal-overlay'),
        $wprCloseModal = $('.wpr-Modal-close, .wpr-Modal-cancel'),
        $wprOpenModal = $('.plugins .deactivate')
    ;

    $wprOpenModal.click(function() {
        wprOpenModal();
        return false;
    });

    $wprCloseModal.click(function() {
        wprCloseModal();
        return false;
    });

    function wprOpenModal(){
        $wprModal.css('display','block');
        $wprModalOverlay.css('display','block');
    }

    function wprCloseModal(){
        $wprModal.css('display','none');
        $wprModalOverlay.css('display','none');
    }



});
