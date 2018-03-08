var $ = jQuery;
$(document).ready(function(){


    /***
    * Dashboard notice
    ***/

    var $notice = $('.wpr-notice');
    var $noticeClose = $('.wpr-notice-close');

    $noticeClose.click(function() {
        wprCloseDashboardNotice();
        return false;
    });

    function wprCloseDashboardNotice(){
        var vTL = new TimelineLite()
          .to($notice, 1, {autoAlpha:0, x:40, ease:Power4.easeOut})
          .to($notice, 0.6, {height: 0, marginTop:0, ease:Power4.easeOut}, '=-.4')
          .set($notice, {'display':'none'})
        ;
    }




    /***
    * Hide / show cloudflare tab
    ***/

    var $checkboxCloudflare = $('#do_cloudflare');
    var $menuItemCloudflare = $('.wpr-cloudflareToggle');

    $checkboxCloudflare.change(function() {
        wprDetectCloudflare();
    }).trigger('change');

    function wprDetectCloudflare(){
        if($checkboxCloudflare.is(':checked')){
            $menuItemCloudflare.css('display','block');
        }
        else{
            $menuItemCloudflare.css('display','none');
        }
    }



});
