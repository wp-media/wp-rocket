<?php

if( isset( $_SERVER['HTTP_USER_AGENT'] )
	&& $_SERVER['REQUEST_METHOD'] == 'GET' 				// Only cache page call with GET method
	&& empty( $_GET )  									// Don't cache page with query variable
	&& !strpos( $_SERVER['REQUEST_URI'], '/feed/' )		// Don't cache feeds
	&& !strpos( $_SERVER['REQUEST_URI'], '.') !== false
	&& !preg_match( '#({{COOKIES_NOT_CACHED}})#', var_export( $_COOKIE , true ) ) // Don't cache page with this cookie
) {

    // Get HTML
	$buffer = file_get_contents( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	// Checking the status of the request
	if( strstr( $http_response_header[0], '200' )!=false ) {

		// Create folder if not already exist
    	if( !is_dir( '{{CACHE_DIR}}/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) )
    		mkdir( '{{CACHE_DIR}}/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'], 0755, true );

    	
    	$paths = array(
    		'WP_ROCKET_PATH'       => '{{WP_ROCKET_PATH}}',
    		'WP_ROCKET_URL'       => '{{WP_ROCKET_URL}}',
    		'WP_ROCKET_CACHE_URL' => '{{WP_ROCKET_CACHE_URL}}',
    		'CACHE_DIR'           => '{{CACHE_DIR}}'
    	);
    	
    	
    	// Concatenate and minify internal css files
    	require '{{WP_ROCKET_FRONT_PATH}}minify.php';
    	
    	list( $buffer, $conditionals ) = rocket_extract_ie_conditionals( $buffer );
    	$buffer = rocket_minyfy_inline_css( $buffer, $paths );
    	$buffer = rocket_minyfy_css( $buffer, $paths );
    	$buffer = rocket_inject_ie_conditionals( $buffer, $conditionals );
    	
    	
    	// Concatenate and minify internal javascript files
    	$internal_js = array();

    	preg_match_all( '/<script.+src=.+(\.js).+><\/script>/i', $buffer, $script_tags_match );

	    foreach ( $script_tags_match[0] as $script_tag ) {

			preg_match('/src=[\'"]([^\'"]+)/', $script_tag, $href_match);

			if ( $href_match[1] ) {

				$url = str_replace( 'http://' . $_SERVER['HTTP_HOST'], '', $href_match[1]);

				if( substr($url, 0, 4 ) != 'http' ) {

				    $buffer = str_replace($script_tag, '', $buffer);
				    $internal_js[] = ltrim( preg_replace( '#\?.*$#', '', $url ), '/' );
				}

			}

	    }

	    $minify_js = 'http://' . $_SERVER['HTTP_HOST'] . '/wp-content/plugins/wp-rocket/min/f=' . implode(',', $internal_js);

    	// Insert the minify css file
    	$buffer = preg_replace('/<\/head>/', '<script src="'.$minify_js.'"></script>\\0', $buffer, 1);


    	// Minify HTML
    	require( 'min/lib/Minify/HTML.php' );


    	// Create file cache
		file_put_contents( '{{CACHE_DIR}}/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html', Minify_HTML::minify( $buffer ) );


		// Read and display file
		readfile( '{{CACHE_DIR}}/' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '/index.html' );
		exit;

	}
	else {
		
		// Tells WordPress to load the WordPress theme and output it.
		define('WP_USE_THEMES', true);

		/** Loads the WordPress Environment and Template */
        require( '{{ABSPATH}}wp-blog-header.php' );
		exit;
	}

}
else {

	// Tells WordPress to load the WordPress theme and output it.
	define('WP_USE_THEMES', true);

	/** Loads the WordPress Environment and Template */
    require( '{{ABSPATH}}wp-blog-header.php' );
	exit;

}