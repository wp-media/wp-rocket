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





    /***
    * Show popin analytics
    ***/

    var $wprPopin = $('.wpr-Popin'),
        $wprPopinOverlay = $('.wpr-Popin-overlay'),
        $wprClosePopin = $('.wpr-Popin-close'),
        $wprOpenPopin = $('.wpr-js-popin')
    ;

    $wprOpenPopin.click(function() {
        wprOpenAnalytics();
        return false;
    });

    $wprClosePopin.click(function() {
        wprCloseAnalytics();
        return false;
    });

    function wprOpenAnalytics(){
        var vTL = new TimelineLite()
          .set($wprPopin, {'display':'block'})
          .set($wprPopinOverlay, {'display':'block'})
          .fromTo($wprPopinOverlay, 0.6, {autoAlpha:0},{autoAlpha:1, ease:Power4.easeOut})
          .fromTo($wprPopin, 0.6, {autoAlpha:0, marginTop: -24}, {autoAlpha:1, marginTop:0, ease:Power4.easeOut}, '=-.5')
        ;
    }

    function wprCloseAnalytics(){
        var vTL = new TimelineLite()
          .fromTo($wprPopin, 0.6, {autoAlpha:1, marginTop: 0}, {autoAlpha:0, marginTop:-24, ease:Power4.easeOut})
          .fromTo($wprPopinOverlay, 0.6, {autoAlpha:1},{autoAlpha:0, ease:Power4.easeOut}, '=-.5')
          .set($wprPopin, {'display':'none'})
          .set($wprPopinOverlay, {'display':'none'})
        ;
    }


});
