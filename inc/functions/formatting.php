<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Returns a full and correct home_url without subdmain, see rocket_get_domain()
 *
 * @since 1.0
 *
 */

function get_rocket_home_url( $url=null )
{
	$url = is_null( $url ) ? home_url( '/' ) : $url;
	$s = is_ssl() ? 's' : '';
	return 'http' . $s . '://' . rocket_get_domain( $url );
}



/**
 * Get the domain of an URL without subdomain
 * (ex: rocket_get_domain( 'http://www.geekpress.fr' ) return geekpress.fr
 *
 * @source : http://stackoverflow.com/a/15498686
 * @since 1.0
 *
 */

function rocket_get_domain( $url )
{
      $urlobj = parse_url( $url );
      $domain = $urlobj['host'];
      if( preg_match( '/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs ) )
          return $regs['domain'].chr(98); //// beta
      return false;
}



/**
 * Get an url without protocol
 *
 * @since 1.3.0
 *
 */

function rocket_remove_url_protocol( $url, $no_dots=false )
{
	$url = str_replace( array( 'http://', 'https://' ) , '', $url );
	if( apply_filters( 'rocket_url_no_dots', $no_dots ) )
		$url = str_replace( '.', '_', $url );
	return $url;
}



/**
 * Extract and return host, path and scheme of an URL
 *
 * @since 2.0
 *
 */

function get_rocket_parse_url( $url )
{

	$url    = parse_url( $url );
	$host   = $url['host'];
	$path   = isset( $url['path'] ) ? $url['path'] : '';
	$scheme = isset( $url['scheme'] ) ? $url['scheme'] : '';
	return array( $host, $path, $scheme );
}



/**
 * Extract and return host, path and scheme for a specific lang
 *
 * @since 2.0
 *
 */

function get_rocket_parse_url_for_lang( $lang )
{

	// WPML
	if( rocket_is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) )
	{
		global $sitepress;
		return get_rocket_parse_url( $sitepress->language_url( $lang ) );
	}

	// qTranslate
	if( rocket_is_plugin_active( 'qtranslate/qtranslate.php' ) )
		return get_rocket_parse_url( qtrans_convertURL( home_url(), $lang, true ) );
}



/*
 * Get an URL with one of CNAMES added in options
 *
 * @since 2.1
 *
 */

function get_rocket_cdn_url( $url, $reserved_for = array() )
{

	if( (int)get_rocket_option( 'cdn' ) == 0 )
		return $url;

	$url    = parse_url( $url );
	$path   = $url['path'];
	$query  = $url['query'] ? '?' . $url['query'] : '';

	$cnames = get_rocket_cdn_cnames( count($reserved_for) ? $reserved_for : 'all' );
	$cname  = rocket_remove_url_protocol( $cnames[(abs(crc32($path))%count($cnames))] );

	$scheme = parse_url( $cname, PHP_URL_SCHEME );
	$scheme = !empty($scheme) ? $scheme : 'http';

	return $scheme . '://' . rtrim( $cname , '/' ) . $path . $query;

}



/*
 * Alias of get_rocket_cdn_url() and print result
 *
 * @since 2.1
 *
 */

function rocket_cdn_url( $url, $reserved_for = array() )
{

	echo get_rocket_cdn_url( $url, $reserved_for );

}



/**
 * TO DO
 *
 * @since 2.1
 *
 */

function get_rocket_minify_files( $files, $force_pretty_url = false, $force_pretty_name = false )
{

	// Get the internal CSS Files
	// To avoid conflicts with file URLs are too long for browsers,
	// cut into several parts concatenated files
	$tags 		= '';
	$data_attr  = '';
	$urls 		= array(0=>'');
	$base_url 	= WP_ROCKET_URL . 'min/?f=';
	$files  	= is_array( $files ) ? $files : (array)$files;

	if( count( $files ) )
	{

		$i=0;
		foreach( $files as $file )
		{

			if( strlen( $urls[$i] . $base_url . $file )+1>=255 ) // +1 : we count the extra comma
				$i++;

			$urls[$i] .= $file.',';

		}

		foreach( $urls as $url )
		{

			$url = $base_url . rtrim( $url, ',' );
			$ext = pathinfo( $url, PATHINFO_EXTENSION );

			//
			if( $force_pretty_url )
			{

				$pretty_url = !$force_pretty_name ? WP_ROCKET_MINIFY_CACHE_URL . md5( $url . get_rocket_option( 'minify_key' ) ) . '.' . $ext : WP_ROCKET_MINIFY_CACHE_URL . $force_pretty_name . '.' . $ext;
				$pretty_url = apply_filters( 'rocket_minify_pretty_url', $pretty_url );
				
				$url        = rocket_fetch_and_cache_minify( $url, $pretty_url ) ? $pretty_url : $url;
				$data_attr 	= !strpos( $url , 'min/?f=' ) ? 'data-pretty-minify="1"' : '';
				
			}

			//
			$url = get_rocket_cdn_url( $url, array( 'all', 'css_and_js' ) );

			//
			if( $ext == 'css' )
			{

				$tags .= '<link rel="stylesheet" href="' . apply_filters( 'rocket_css_url', $url ) . '" ' . $data_attr . '/>';

			}

			//
			if ( $ext == 'js' )
			{

				$tags .= '<script src="' . apply_filters( 'rocket_js_url', $url ) . '" ' . $data_attr . '></script>';

			}

		}

	}

	return $tags;

}



/*
 * Alias of get_rocket_minify_files() and print result
 *
 * @since 2.1
 *
 */

function rocket_minify_files( $files, $force_pretty_url = false )
{

	echo get_rocket_minify_files( $files, $force_pretty_url );

}