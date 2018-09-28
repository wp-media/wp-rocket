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

	/**
	 * Hide / show Rocket addon tabs.
	 */
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
