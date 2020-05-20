let checkCPCSSGenerationCall;
let cpcsssGenerationPending  = 0;
const rocketDeleteCPCSSbtn   = document.getElementById( 'rocket-delete-post-cpss' );
const rocketGenerateCPCSSbtn = document.getElementById( 'rocket-generate-post-cpss' );
const rocketCPCSSGenerate    = document.querySelectorAll( '.cpcss_generate' );
const rocketCPCSSReGenerate  = document.querySelectorAll( '.cpcss_regenerate' );

rocketDeleteCPCSSbtn.addEventListener( 'click', e => {
	e.preventDefault();
	deleteCPCSS();
} );

rocketGenerateCPCSSbtn.addEventListener( 'click', e => {
	e.preventDefault();
	rocketGenerateCPCSSbtn.disabled = true;
	checkCPCSSGeneration();
} );

const checkCPCSSGeneration = ( timeout = null ) => {
	const spinner                   = rocketGenerateCPCSSbtn.querySelector( '.spinner' );
	spinner.style.display           = 'block';
	spinner.style.visibility        = 'visible';

	const xhttp  = new XMLHttpRequest();
	xhttp.onload = () => {
		if ( 200 !== xhttp.status ) {
			return;
		}

		const cpcss_response = JSON.parse( xhttp.response );
		if ( 200 !== cpcss_response.data.status ) {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'error' );
			rocketGenerateCPCSSbtn.disabled = false;
			return;
		}

		if ( 200 === cpcss_response.data.status &&
			'cpcss_generation_pending' !== cpcss_response.code ) {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'success' );

			// Revert view to Regenerate.
			rocketGenerateCPCSSbtn.querySelector( '.rocket-generate-post-cpss-btn-txt' ).innerHTML = rocket_cpcss.regenerate_btn;
			rocketDeleteCPCSSbtn.style.display                                                     = 'block';
			rocketGenerateCPCSSbtn.disabled                                                        = false;
			rocketCPCSSGenerate.forEach( item => item.style.display = 'none' );
			rocketCPCSSReGenerate.forEach( item => item.style.display = 'block' );
			return;
		}

		cpcsssGenerationPending++;

		if ( cpcsssGenerationPending > 10 ) {
			stopCPCSSGeneration( spinner );
			cpcsssGenerationPending = 0;
			checkCPCSSGeneration( true );
			return;
		}

		checkCPCSSGenerationCall = setTimeout( () => {
			checkCPCSSGeneration();
		}, 3000 );
	};

	xhttp.open( 'POST', rocket_cpcss.rest_url, true );
	xhttp.setRequestHeader( 'Content-Type', 'application/json;charset=UTF-8' );
	xhttp.setRequestHeader( 'X-WP-Nonce', rocket_cpcss.rest_nonce );
	xhttp.send( JSON.stringify( { timeout: timeout } ) );
}

const stopCPCSSGeneration = ( spinner ) => {
	spinner.style.display = 'none';
	clearTimeout( checkCPCSSGenerationCall );
}

const deleteCPCSS = () => {
	rocketDeleteCPCSSbtn.disabled = true;

	const xhttp  = new XMLHttpRequest();
	xhttp.onload = () => {
		if ( 200 !== xhttp.status ) {
			return;
		}

		rocketDeleteCPCSSbtn.disabled = false;
		const cpcss_response          = JSON.parse( xhttp.response );

		if ( 200 !== cpcss_response.data.status ) {
			cpcssNotice( cpcss_response.message, 'error' );
			return;
		}
		cpcssNotice( cpcss_response.message, 'success' );

		// Revert view to Generate.
		rocketGenerateCPCSSbtn.querySelector( '.rocket-generate-post-cpss-btn-txt' ).innerHTML = rocket_cpcss.generate_btn;
		rocketDeleteCPCSSbtn.style.display                                                     = 'none';
		rocketCPCSSReGenerate.forEach( item => item.style.display = 'none' );
		rocketCPCSSGenerate.forEach( item => item.style.display = 'block' );
	};

	xhttp.open( 'DELETE', rocket_cpcss.rest_url, true );
	xhttp.setRequestHeader( 'Content-Type', 'application/json;charset=UTF-8' );
	xhttp.setRequestHeader( 'X-WP-Nonce', rocket_cpcss.rest_nonce );
	xhttp.send();
}

const cpcssNotice = ( msg, type ) => {
	/* Add notice class */
	const cpcssNotice     = document.getElementById( 'cpcss_response_notice' );
	cpcssNotice.innerHTML = '';
	cpcssNotice.classList.remove( 'hidden', 'notice', 'notice-error', 'notice-success' );
	cpcssNotice.classList.add( 'notice', 'notice-' + type );

	/* create paragraph element to hold message */
	const p = document.createElement( 'p' );
	p.appendChild( document.createTextNode( msg ) );

	/* Add the whole message to notice div */
	cpcssNotice.appendChild( p );
}
