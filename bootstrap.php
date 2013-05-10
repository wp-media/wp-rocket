<?php

// Dont' cache feeds !
$is_not_feed = strpos( $_SERVER['REQUEST_URI'], '/feed/' ) === false ? true : false;

// Don't cache page with this cookie
$is_allowed_cookie = preg_match( '/(wp-postpass_|wordpress_logged_in)/', var_export( $_COOKIE , true ) ) ? false : true;


if( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['REQUEST_METHOD'] == 'GET' && empty( $_GET ) && $is_not_feed && $is_allowed_cookie )
{

    // Get HTML
	$data = @file_get_contents( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );

	// Checking the status of the request
	if( strstr( $http_response_header[0], '200' )!=false )
	{
		// Create folder if not already exist
    	if( !is_dir( '/Applications/MAMP/htdocs/tuto-wordpress/wp-content/plugins/wp-rocket/cache' . $_SERVER['REQUEST_URI'] ) )
    		mkdir( '/Applications/MAMP/htdocs/tuto-wordpress/wp-content/plugins/wp-rocket/cache' . $_SERVER['REQUEST_URI'], 0755, true );


    	// Create file cache
		file_put_contents( '/Applications/MAMP/htdocs/tuto-wordpress/wp-content/plugins/wp-rocket/cache' . $_SERVER['REQUEST_URI'] . '/index.html.gz', gzencode( $data, 6 ) );


		// Set Encoding to Gzip
		header( "Content-Encoding: gzip" );


		// Read and display file
		readfile( '/Applications/MAMP/htdocs/tuto-wordpress/wp-content/plugins/wp-rocket/cache' . $_SERVER['REQUEST_URI'] . '/index.html.gz' );
		exit;

	}
	else
	{
		// Tells WordPress to load the WordPress theme and output it.
		define('WP_USE_THEMES', true);

		/** Loads the WordPress Environment and Template */
        require( '/Applications/MAMP/htdocs/tuto-wordpress/wp-blog-header.php' );
		exit;
	}

}
else
{

	// Tells WordPress to load the WordPress theme and output it.
	define('WP_USE_THEMES', true);

	/** Loads the WordPress Environment and Template */
    require( '/Applications/MAMP/htdocs/tuto-wordpress/wp-blog-header.php' );
	exit;

}