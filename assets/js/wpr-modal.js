var $ = jQuery;
$(document).ready(function(){

    var $wprModal = $(".wpr-Modal");
    if($wprModal){
        new ModalWpr($wprModal);
    }

});


/*-----------------------------------------------*\
		CLASS ModalWpr
\*-----------------------------------------------*/
/**
 * Manages the display of deactivation modal box
 *
 * Public method :
	 open - Open the modal
     close - Close the modal
 *
 */

function ModalWpr(aElem) {

    var refThis = this;

    this.elem = aElem;
    this.overlay = $('.wpr-Modal-overlay');
    this.closer = $('.wpr-Modal-close, .wpr-Modal-cancel', aElem);
    this.opener = $('.plugins .deactivate');


    this.opener.click(function() {
        refThis.open();
        return false;
    });

    this.closer.click(function() {
        refThis.close();
        return false;
    });

}


/*
* Open modal
*/
ModalWpr.prototype.open = function() {

    this.elem.css('display','block');
    this.overlay.css('display','block');

};


/*
* Close modal
*/
ModalWpr.prototype.close = function() {

    this.elem.css('display','none');
    this.overlay.css('display','none');

};
