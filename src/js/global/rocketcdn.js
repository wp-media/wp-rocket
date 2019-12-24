document.addEventListener( 'DOMContentLoaded', function() {
    document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( function(el) {
        el.addEventListener( 'click', function(e) {
            e.preventDefault();
        });
    });

    MicroModal.init({
        disableScroll: true
    });
});

window.addEventListener('load', function() {
    var openCTA  = document.querySelector( '#wpr-rocketcdn-open-cta' ),
        closeCTA = document.querySelector( '#wpr-rocketcdn-close-cta' ),
        smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' ),
        bigCTA   = document.querySelector( '#wpr-rocketcdn-cta' );

    if ( null !== openCTA ) {
        openCTA.addEventListener('click', function(e) {
            e.preventDefault();

            smallCTA.classList.add('wpr-isHidden');
            bigCTA.classList.remove('wpr-isHidden');

            var httpRequest = new XMLHttpRequest(),
            postData = '';

            postData += 'action=toggle_rocketcdn_cta';
            postData += '&status=big';
            postData += '&nonce=' + rocket_ajax_data.nonce;

            httpRequest.open( 'POST', ajaxurl );
            httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
            httpRequest.send( postData );
        });
    }

    if ( null !== closeCTA && null !== smallCTA && null !== bigCTA ) {
        closeCTA.addEventListener('click', function(e) {
            e.preventDefault();

            smallCTA.classList.remove('wpr-isHidden');
            bigCTA.classList.add('wpr-isHidden');

            var httpRequest = new XMLHttpRequest(),
            postData = '';

            postData += 'action=toggle_rocketcdn_cta';
            postData += '&status=small';
            postData += '&nonce=' + rocket_ajax_data.nonce;

            httpRequest.open( 'POST', ajaxurl );
            httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
            httpRequest.send( postData );
        });
    }
});

window.onmessage = (e) => {
	if ( e.origin == 'https://dave.wp-rocket.me' ) {
		if (e.data.hasOwnProperty("cdnFrameHeight")) {
			document.getElementById("rocketcdn-iframe").style.height = `${e.data.cdnFrameHeight}px`;
		}
		if (e.data.hasOwnProperty("cdnFrameClose")) {
			MicroModal.close('wpr-rocketcdn-modal');
		}
	}
};
