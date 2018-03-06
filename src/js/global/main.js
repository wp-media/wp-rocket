document.addEventListener( 'DOMContentLoaded', function () {


    /***
    * Dashboard notice
    ***/

    var $notice = document.querySelector('.wpr-notice');
    var $noticeClose = $notice.querySelector('.wpr-notice-close');

    $noticeClose.onclick = function(){
        var vTL = new TimelineLite()
          .to($notice, 1, {autoAlpha:0, x:40, ease:Power4.easeOut})
          .to($notice, 0.6, {height: 0, marginTop:0, ease:Power4.easeOut}, '=-.4')
          .set($notice, {'display':'none'})
        ;

        return false;
    }


});
