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
	checkCPCSSGeneration();
} );

const checkCPCSSGeneration = ( timeout = null ) => {
	const spinner            = rocketGenerateCPCSSbtn.querySelector( '.spinner' );
	spinner.style.display    = 'block';
	spinner.style.visibility = 'visible';

	const xhttp              = new XMLHttpRequest();
	xhttp.onreadystatechange = () => {
		if ( this.readyState !== 4 || this.status !== 200 ) {
			return;
		}

		const cpcss_response = JSON.parse( this.responseText );
		if ( cpcss_response.data.status !== 200 ) {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'error' );
			return;
		}

		if ( cpcss_response.data.status === 200 && cpcss_response.code !== 'cpcss_generation_pending' ) {
			stopCPCSSGeneration( spinner );
			cpcssNotice( cpcss_response.message, 'success' );

			// Revert view to Regenerate.
			rocketGenerateCPCSSbtn.querySelector( '.rocket-generate-post-cpss-btn-txt' ).innerHTML = cpcss_regenerate_btn;
			rocketDeleteCPCSSbtn.style.display                                                     = 'block';
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

	xhttp.open( 'POST', cpcss_rest_url, true );
	xhttp.setRequestHeader( 'Content-Type', 'application/json;charset=UTF-8' );
	xhttp.setRequestHeader( 'X-WP-Nonce', cpcss_rest_nonce );
	xhttp.send( JSON.stringify( { timeout: timeout } ) );
}

const stopCPCSSGeneration = ( spinner ) => {
	spinner.style.display = 'none';
	clearTimeout( checkCPCSSGenerationCall );
}

const deleteCPCSS = () => {
	const xhttp              = new XMLHttpRequest();
	xhttp.onreadystatechange = () => {
		if ( this.readyState !== 4 | this.status !== 200 ) {
			return;
		}
		const cpcss_response = JSON.parse( this.responseText );
		if ( cpcss_response.data.status !== 200 ) {
			cpcssNotice( cpcss_response.message, 'error' );
			return;
		}
		cpcssNotice( cpcss_response.message, 'success' );

		// Revert view to Generate.
		rocketGenerateCPCSSbtn.querySelector( '.rocket-generate-post-cpss-btn-txt' ).innerHTML = cpcss_generate_btn;
		rocketDeleteCPCSSbtn.style.display                                                     = 'none';
		rocketCPCSSReGenerate.forEach( item => item.style.display = 'none' );
		rocketCPCSSGenerate.forEach( item => item.style.display = 'block' );
	};

	xhttp.open( 'DELETE', cpcss_rest_url, true );
	xhttp.setRequestHeader( 'Content-Type', 'application/json;charset=UTF-8' );
	xhttp.setRequestHeader( 'X-WP-Nonce', cpcss_rest_nonce );
	xhttp.send();
}

const cpcssNotice = ( msg, type ) => {
	/* Add notice class */
	const cpcssNotice     = document.getElementById( 'cpcss_response_notice' );
	cpcssNotice.innerHTML = '';
	cpcssNotice.classList.remove( 'notice', 'notice-error', 'notice-success' );
	cpcssNotice.classList.add( 'notice', 'notice-' + type );

	/* create paragraph element to hold message */
	const p = document.createElement( 'p' );
	p.appendChild( document.createTextNode( msg ) );

	/* Add the whole message to notice div */
	cpcssNotice.appendChild( p );
}
