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
	* Hide / show Rocket addon tabs.
	***/

	$( '.wpr-toggle-button' ).each( function() {
		var $button   = $( this );
		var $checkbox = $button.closest( '.wpr-fieldsContainer-fieldset' ).find( '.wpr-radio :checkbox' );
		var $menuItem = $( '[href="' + $button.attr( 'href' ) + '"].wpr-menuItem' );

		$checkbox.change( function() {
			if ( $checkbox.is( ':checked' ) ) {
				$menuItem.css( 'display', 'block' );
				$button.css( 'display', 'inline-block' );
			} else{
				$menuItem.css( 'display', 'none' );
				$button.css( 'display', 'none' );
			}
		} ).trigger( 'change' );
	} );





	/***
	* Show popin analytics
	***/

	var $wprAnalyticsPopin = $('.wpr-Popin-Analytics'),
		$wprPopinOverlay = $('.wpr-Popin-overlay'),
		$wprAnalyticsClosePopin = $('.wpr-Popin-Analytics-close'),
		$wprAnalyticsPopinButton = $('.wpr-Popin-Analytics .wpr-button'),
		$wprAnalyticsOpenPopin = $('.wpr-js-popin')
	;

	$wprAnalyticsOpenPopin.click(function(e) {
		e.preventDefault();
		wprOpenAnalytics();
		return false;
	});

	$wprAnalyticsClosePopin.click(function(e) {
		e.preventDefault();
		wprCloseAnalytics();
		return false;
	});

	$wprAnalyticsPopinButton.click(function(e) {
		e.preventDefault();
		wprActivateAnalytics();
		return false;
	});

	function wprOpenAnalytics(){
		var vTL = new TimelineLite()
		  .set($wprAnalyticsPopin, {'display':'block'})
		  .set($wprPopinOverlay, {'display':'block'})
		  .fromTo($wprPopinOverlay, 0.6, {autoAlpha:0},{autoAlpha:1, ease:Power4.easeOut})
		  .fromTo($wprAnalyticsPopin, 0.6, {autoAlpha:0, marginTop: -24}, {autoAlpha:1, marginTop:0, ease:Power4.easeOut}, '=-.5')
		;
	}

	function wprCloseAnalytics(){
		var vTL = new TimelineLite()
		  .fromTo($wprAnalyticsPopin, 0.6, {autoAlpha:1, marginTop: 0}, {autoAlpha:0, marginTop:-24, ease:Power4.easeOut})
		  .fromTo($wprPopinOverlay, 0.6, {autoAlpha:1},{autoAlpha:0, ease:Power4.easeOut}, '=-.5')
		  .set($wprAnalyticsPopin, {'display':'none'})
		  .set($wprPopinOverlay, {'display':'none'})
		;
	}

	function wprActivateAnalytics(){
		wprCloseAnalytics();
		$('#analytics_enabled').attr('checked', true);
		$('#analytics_enabled').trigger('change');
	}

	/***
	* Show popin beta test
	***/

	var $wprBetaPopin = $('.wpr-Popin-Beta'),
	$wprBetaClosePopin = $('.wpr-Popin-Beta-close'),
	$wprBetaPopinButton = $('.wpr-Popin-Beta .wpr-button'),
	$wprBetaOpenPopin = $('#do_beta');

	$wprBetaOpenPopin.change(function(e) {
		if ($wprBetaOpenPopin.is(':checked')) {
			wprOpenBeta();
			return false;
		}
	});

	$wprBetaClosePopin.click(function() {
		wprDeactivateBeta();
		return false;
	});

	$wprBetaPopinButton.click(function() {
		wprCloseBeta();
		return false;
	});

	function wprOpenBeta(){
		var vTL = new TimelineLite()
			.set($wprBetaPopin, {'display':'block'})
			.set($wprPopinOverlay, {'display':'block'})
			.fromTo($wprPopinOverlay, 0.6, {autoAlpha:0},{autoAlpha:1, ease:Power4.easeOut})
			.fromTo($wprBetaPopin, 0.6, {autoAlpha:0, marginTop: -24}, {autoAlpha:1, marginTop:0, ease:Power4.easeOut}, '=-.5')
		;
	}

	function wprCloseBeta(){
		var vTL = new TimelineLite()
			.fromTo($wprBetaPopin, 0.6, {autoAlpha:1, marginTop: 0}, {autoAlpha:0, marginTop:-24, ease:Power4.easeOut})
			.fromTo($wprPopinOverlay, 0.6, {autoAlpha:1},{autoAlpha:0, ease:Power4.easeOut}, '=-.5')
			.set($wprBetaPopin, {'display':'none'})
			.set($wprPopinOverlay, {'display':'none'})
		;
	}

	function wprDeactivateBeta(){
		wprCloseBeta();
		$('#do_beta').attr('checked', false);
		$('#do_beta').trigger('change');
	}

	/***
	* Sidebar on/off
	***/
	var $wprSidebar    = $( '.wpr-Sidebar' );
	var $wprButtonTips = $('.wpr-js-tips');

	$wprButtonTips.change(function() {
		wprDetectTips($(this));
	});

	function wprDetectTips(aElem){
		if(aElem.is(':checked')){
			$wprSidebar.css('display','block');
			localStorage.setItem( 'wpr-show-sidebar', 'on' );
		}
		else{
			$wprSidebar.css('display','none');
			localStorage.setItem( 'wpr-show-sidebar', 'off' );
		}
	}



	/***
	* Detect Adblock
	***/

	if(document.getElementById('LKgOcCRpwmAj')){
		$('.wpr-adblock').css('display', 'none');
	} else {
		$('.wpr-adblock').css('display', 'block');
	}

	var $adblock = $('.wpr-adblock');
	var $adblockClose = $('.wpr-adblock-close');

	$adblockClose.click(function() {
		wprCloseAdblockNotice();
		return false;
	});

	function wprCloseAdblockNotice(){
		var vTL = new TimelineLite()
		  .to($adblock, 1, {autoAlpha:0, x:40, ease:Power4.easeOut})
		  .to($adblock, 0.4, {height: 0, marginTop:0, ease:Power4.easeOut}, '=-.4')
		  .set($adblock, {'display':'none'})
		;
	}

});
