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

    /**
     * Rocket Analytics notice info collect
     */
    $( '.rocket-analytics-data-container' ).hide();
	$( '.rocket-preview-analytics-data' ).on( 'click', function( e ) {
		e.preventDefault();

		$(this).parent().next( '.rocket-analytics-data-container' ).toggle();
	} );

    /***
    * Hide / show cloudflare tab
    ***/

    var $checkboxCloudflare = $('#do_cloudflare');
    var $cloudflareToggle = $('.wpr-cloudflareToggle');
    var $cloudflareToggleButton = $('.wpr-cloudflareToggleButton');

    $checkboxCloudflare.change(function() {
        wprDetectCloudflare();
    }).trigger('change');

    function wprDetectCloudflare(){
        if($checkboxCloudflare.is(':checked')){
            $cloudflareToggle.css('display','block');
            $cloudflareToggleButton.css('display','inline-block');
        }
        else{
            $cloudflareToggle.css('display','none');
            $cloudflareToggleButton.css('display','none');
        }
    }





    /***
    * Show popin analytics
    ***/

    var $wprPopin = $('.wpr-Popin'),
        $wprPopinOverlay = $('.wpr-Popin-overlay'),
        $wprClosePopin = $('.wpr-Popin-close'),
        $wprPopinButton = $('.wpr-Popin .wpr-button'),
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

    $wprPopinButton.click(function() {
        wprActivateAnalytics();
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

    function wprActivateAnalytics(){
        wprCloseAnalytics();
        $('#analytics_enabled').attr('checked', true);
        $('#analytics_enabled').trigger('change');
    }




    /***
    * Tips on/off
    ***/

    var $wprButtonTips = $('.wpr-js-tips');

    $wprButtonTips.change(function() {
        wprDetectTips($(this));
    }).trigger('change');

    function wprDetectTips(aElem){
        if(aElem.is(':checked')){
            console.log('show');
            $('.wpr-field-description').css('display','block');
        }
        else{
            console.log('hide');
            $('.wpr-field-description').css('display','none');
        }
    }


});
