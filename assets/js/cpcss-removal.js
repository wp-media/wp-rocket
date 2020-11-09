const wprRemoveCPCSS = () => {
	if ( document.querySelector( 'link[data-rocket-async="style"][rel="preload"]' ) ) {
		setTimeout( wprRemoveCPCSS, 200 );
	} else {
		const elem = document.getElementById( 'rocket-critical-css' );
		if ( elem && 'remove' in elem ) {
			elem.remove();
		}
	}
};

if ( window.addEventListener ) {
	window.addEventListener( 'load', wprRemoveCPCSS );
} else if ( window.attachEvent ) {
	window.attachEvent( 'onload', wprRemoveCPCSS );
}
