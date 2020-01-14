document.addEventListener( 'DOMContentLoaded', function() {
    document.querySelectorAll( '.wpr-rocketcdn-open' ).forEach( function(el) {
        el.addEventListener( 'click', function(e) {
            e.preventDefault();
        });
    });

    MicroModal.init({
        disableScroll: true,
        onClose: function() {
            document.location.reload();
        }
    });
});

window.addEventListener('load', function() {
    var openCTA  = document.querySelector( '#wpr-rocketcdn-open-cta' ),
        closeCTA = document.querySelector( '#wpr-rocketcdn-close-cta' ),
        smallCTA = document.querySelector( '#wpr-rocketcdn-cta-small' ),
        bigCTA   = document.querySelector( '#wpr-rocketcdn-cta' );

    if ( null !== openCTA && null !== smallCTA && null !== bigCTA ) {
        openCTA.addEventListener('click', function(e) {
            e.preventDefault();

            smallCTA.classList.add('wpr-isHidden');
            bigCTA.classList.remove('wpr-isHidden');

            rocketSendHTTPRequest( rocketGetPostData( 'big' ) );
        });
    }

    if ( null !== closeCTA && null !== smallCTA && null !== bigCTA ) {
        closeCTA.addEventListener('click', function(e) {
            e.preventDefault();

            smallCTA.classList.remove('wpr-isHidden');
            bigCTA.classList.add('wpr-isHidden');

            rocketSendHTTPRequest( rocketGetPostData( 'small' ) );
        });
    }

    function rocketGetPostData( status ) {
        let postData = '';
        
        postData += 'action=toggle_rocketcdn_cta';
        postData += '&status=' + status;
        postData += '&nonce=' + rocket_ajax_data.nonce;
        
        return postData;
    }
});

window.onmessage = (e) => {
    let iframeURL = 'https://dave.wp-rocket.me';

	if ( e.origin == iframeURL ) {
		if (e.data.hasOwnProperty("cdnFrameHeight")) {
            document.getElementById("rocketcdn-iframe").style.height = `${e.data.cdnFrameHeight}px`;
            return;
        }

		if (e.data.hasOwnProperty("cdnFrameClose")) {
            MicroModal.close('wpr-rocketcdn-modal');
            return;
        }

        let iframe = document.querySelector('#rocketcdn-iframe').contentWindow;

        if (e.data.hasOwnProperty("cdn_token")) {
            let postData = '',
            request = '';

            postData += 'action=save_rocketcdn_token';
            postData += '&value=' + e.data.cdn_token;
            postData += '&nonce=' + rocket_ajax_data.nonce;

            request = rocketSendHTTPRequest( postData );

            request.onreadystatechange = function() {
                if (request.readyState === XMLHttpRequest.DONE) {
                    if (request.status === 200) {
                        iframe.postMessage(
                            request.responseText,
                            iframeURL
                        );
                    }
                }
            };
        } else {
           iframe.postMessage(
               {
                   'success': false,
                   'data': 'token_not_received'
               },
               iframeURL
           );
        }
	}
};

function rocketSendHTTPRequest( postData ) {
    const httpRequest = new XMLHttpRequest();
    
    httpRequest.open( 'POST', ajaxurl );
    httpRequest.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' )
    httpRequest.send( postData );

    return httpRequest;
}