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
     change - Test if modal state change
 *
 */

function ModalWpr(aElem) {

    var refThis = this;

    this.elem = aElem;
    this.overlay = $('.wpr-Modal-overlay');
    this.radio = $('input[name=reason]', aElem);
    this.closer = $('.wpr-Modal-close, .wpr-Modal-cancel', aElem);
    this.return = $('.wpr-Modal-return', aElem);
    this.opener = $('.plugins .deactivate');
    this.question = $('.wpr-Modal-question', aElem);
    this.button = $('.button-primary', aElem);
    this.title = $('.wpr-Modal-header h2', aElem);
    this.titleText = this.title.text();


    this.opener.click(function() {
        refThis.open();
        return false;
    });

    this.closer.click(function() {
        refThis.close();
        return false;
    });

    this.return.click(function() {
        refThis.returnToQuestion();
        return false;
    });

    this.radio.change(function(){
        refThis.change($(this));
    });

}


/*
* Change modal stage
*/
ModalWpr.prototype.change = function(aElem) {

    var id = aElem.attr('id');
    var refThis = this;
    $('.wpr-Modal-fieldHidden').removeClass('wpr-isOpen');
    $('.wpr-Modal-hidden').removeClass('wpr-isOpen');

    this.button.removeClass('wpr-isDisabled');
    this.button.removeAttr("disabled");

    switch(id){
      case 'reason-temporary':
          // Nothing to do
      break;

      case 'reason-broke':
      case 'reason-score':
      case 'reason-loading':
      case 'reason-complicated':
          var $panel = $('#' + id + '-panel');
          refThis.question.removeClass('wpr-isOpen');
          refThis.return.addClass('wpr-isOpen');
          $panel.addClass('wpr-isOpen');

          var titleText = $panel.find('h3').text();
          this.title.text(titleText);
      break;

      case 'reason-host':
      case 'reason-other':
          var field = aElem.siblings('.wpr-Modal-fieldHidden');
          field.addClass('wpr-isOpen');
          field.find('input, textarea').focus();
      break;
    }
};



/*
* Return to the question
*/
ModalWpr.prototype.returnToQuestion = function() {

    $('.wpr-Modal-fieldHidden').removeClass('wpr-isOpen');
    $('.wpr-Modal-hidden').removeClass('wpr-isOpen');
    this.question.addClass('wpr-isOpen');
    this.return.removeClass('wpr-isOpen');
    this.title.text(this.titleText);

    this.radio.attr('checked', false);
    this.button.addClass('wpr-isDisabled');
    this.button.attr("disabled", true);

};


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

    this.returnToQuestion();
    this.elem.css('display','none');
    this.overlay.css('display','none');

};
