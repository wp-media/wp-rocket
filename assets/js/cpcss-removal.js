function wprRemoveCPCSS() {
	let preload_stylesheets = document.querySelectorAll( 'link[data-rocket-async="style"][rel="preload"]' );
	if ( preload_stylesheets && preload_stylesheets.length > 0 ) {
		for ( let stylesheet_index = 0;stylesheet_index < preload_stylesheets.length;stylesheet_index++ ){
			let media = preload_stylesheets[stylesheet_index].getAttribute('media') || 'all';
			if( window.matchMedia(media).matches ){
				setTimeout( wprRemoveCPCSS, 200 );
				return;
			}
		}
	}

	const elem = document.getElementById( 'rocket-critical-css' );
	if ( elem && 'remove' in elem ) {
		elem.remove();
	}
}

if ( window.addEventListener ) {
	window.addEventListener( 'load', wprRemoveCPCSS );
} else if ( window.attachEvent ) {
	window.attachEvent( 'onload', wprRemoveCPCSS );
}
