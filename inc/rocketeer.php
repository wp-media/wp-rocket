<?php
!defined( 'ABSPATH' ) or die( 'Cheatin\' uh?' );
@set_time_limit( 0 );

// WordPress BootStrap
while( !is_file( 'wp-load.php' ) ) {
	if( is_dir( '..' ) )
		chdir( '..' );
	else
		die('[NOWP]');
}
require_once 'wp-load.php';
require_once 'wp-admin/includes/plugin.php';

if( !function_exists( 'get_home_path') ){
	function get_home_path() {
		$home = get_option( 'home' );
		$siteurl = get_option( 'siteurl' );
		if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
			$wp_path_rel_to_home = str_ireplace( $home, '', $siteurl );
			$pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
			$home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
			$home_path = trailingslashit( $home_path );
		} else {
			$home_path = ABSPATH;
		}

		return str_replace( '\\', '/', $home_path );
	}
}

function rocket_get_heberg()
{
	$base = $_SERVER['DOCUMENT_ROOT'];
	if( substr( $base, 0, 7)=='/homez.' )
		return 'OVH Mutu';
	elseif( substr( $base, 0, 7 )== '/kunden' ) {
		return '1and1';
	}
	return '?';
}

function rocket_obf( $data )
{
	$b = strrev( 'edocne_46esab' );
	$d = $b 
	( maybe_serialize( $data ) );
	return $d;
}

ob_start();
phpinfo();
$phpinfo = array('phpinfo' => array());
if(preg_match_all('#(?:<h2>(?:<a name=".*?">)?(.*?)(?:</a>)?</h2>)|(?:<tr(?: class=".*?")?><t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>(?:<t[hd](?: class=".*?")?>(.*?)\s*</t[hd]>)?)?</tr>)#s', ob_get_clean(), $matches, PREG_SET_ORDER))
    foreach($matches as $match)
        if(strlen($match[1]))
            $phpinfo[$match[1]] = array();
        elseif(isset($match[3]))
            $phpinfo[end(array_keys($phpinfo))][$match[2]] = isset($match[4]) ? array($match[3], $match[4]) : $match[3];
        else
            $phpinfo[end(array_keys($phpinfo))][] = $match[2];

function rocket_get_plugins()
{
	$_plugins = get_plugins();
	$_mu_plugins = get_mu_plugins();
	$_dropins = get_dropins();
	$plugins = array();
	$def = array( 'Name'=>'?', 'Version'=>'?', 'PluginURI'=>'?' );
	foreach( $_plugins as $name => $ar ) {
		$ar = wp_parse_args( array_filter( $ar ), $def );
		if( is_plugin_active( $name ) )
			$plugins['plugins'][$name] = array( 'Name'=>$ar['Name'], 'Version'=> $ar['Version'], 'PluginURI'=>$ar['PluginURI'] );
	}
	foreach( $_mu_plugins as $name => $ar ) {
		$ar = wp_parse_args( array_filter( $ar ), $def );
			$plugins['muplugins'][$name] = array( 'Name'=>$ar['Name'], 'Version'=> $ar['Version'], 'PluginURI'=>$ar['PluginURI'] );
	}
	foreach( $_dropins as $name => $ar ) {
		$ar = wp_parse_args( array_filter( $ar ), $def );
			$plugins['dropins'][$name] = array( 'Name'=>$ar['Name'], 'Version'=> $ar['Version'], 'PluginURI'=>$ar['PluginURI'] );
	}
	return $plugins;
}

if( !defined( 'WP_ROCKET_CACHE_PATH' ) )
	define( 'WP_ROCKET_CACHE_PATH', WP_CONTENT_DIR . '/wp-rocket-cache/' );
if( !defined( 'WP_ROCKET_SLUG' ) )
	define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );

$infos = array( 'date'=> date( 'l d F Y, G:i:s (e P)' ), 'website'=> home_url() );
$rocket_plugin_path = 'wp-rocket/wp-rocket.php';
$infos['rocket']['active'] = is_plugin_active( $rocket_plugin_path );
$infos['rocket']['options'] = get_option( WP_ROCKET_SLUG );
if( $infos['rocket']['options'] )
	$infos['rocket']['options'] = array_filter( $infos['rocket']['options'] );
$infos['rocket']['cache_chmod'] = substr( sprintf( '%o', fileperms( WP_ROCKET_CACHE_PATH ) ), -4 );


$htaccess_file = get_home_path() . '.htaccess';
$infos['htaccess']['ht_version'] = '?';
$infos['htaccess']['info'] = 'en écriture';
if( !file_exists( $htaccess_file ) ){
	$infos['htaccess']['info'] = 'pas de fichier';
}
if( !is_writable( $htaccess_file ) ){
	$infos['htaccess']['info'] = 'pas en écriture';
}
$get_headers = wp_get_http_headers( home_url() );
if( $htaccess_contents = @file_get_contents( $htaccess_file ) ) {
	$infos['htaccess']['content'] = $htaccess_contents;
	$htaccess_contents = explode( "\n", $htaccess_contents );
	foreach( $htaccess_contents as $hc )
		if( strstr( $hc, '# BEGIN WP Rocket v' ) ){
			$infos['htaccess']['ht_version'] = trim( str_replace( '# BEGIN WP Rocket v', '', $hc ) );
			break;
		}

}
$infos['headers']['server'] = !empty( $get_headers['server'] ) ? $get_headers['server'] : '?';
$infos['headers']['via'] = !empty( $get_headers['via'] ) ? $get_headers['via'] : '?';
$infos['headers']['x-powered-by'] = !empty( $get_headers['x-powered-by'] ) ? $get_headers['x-powered-by'] : '?';
$infos['headers']['x-varnish'] = !empty( $get_headers['x-varnish'] ) ? $get_headers['x-varnish'] : '?';
$infos['headers']['cache-control'] = !empty( $get_headers['cache-control'] ) ? $get_headers['cache-control'] : '?';

$infos['server']['SERVER_SOFTWARE'] = $_SERVER['SERVER_SOFTWARE'];
$infos['server']['SERVER_SIGNATURE'] = $_SERVER['SERVER_SIGNATURE'];
$infos['server']['HTTP_CONNECTION'] = $_SERVER['HTTP_CONNECTION'];
$infos['server']['HTTP_CACHE_CONTROL'] = $_SERVER['HTTP_CACHE_CONTROL'];
$infos['server']['DOCUMENT_ROOT'] = $_SERVER['DOCUMENT_ROOT'];
$infos['server']['hebergeur'] = rocket_get_heberg();

$infos['mysql']['MYSQL_VERSION'] = $GLOBALS['wpdb']->db_version();

$infos['php']['PHP_VERSION'] = PHP_VERSION;
$infos['php']['modules'] = '?';
if( isset( $phpinfo['apache2handler']['Loaded Modules'] ) )
	$infos['php']['modules'] = $phpinfo['apache2handler']['Loaded Modules'];

$infos['wordpress']['wp_version'] = $GLOBALS['wp_version'];
$infos['wordpress']['is_multisite'] = is_multisite();
$current_theme = wp_get_theme();
$infos['wordpress']['theme'] = array( 	'Name'=>$current_theme->get( 'Name' ),
										'ThemeURI'=>$current_theme->get( 'ThemeURI' ),
										'Version'=>$current_theme->get( 'Version' ),
									);
$infos['wordpress']['plugins_active'] = rocket_get_plugins();
$infos['wordpress']['charset'] = get_bloginfo( 'charset', 'display' );
$infos['wordpress']['DEFINES'] = array( 
										'WP_CACHE' => defined( 'WP_CACHE' ) ? WP_CACHE : false, 
										'WP_MEMORY_LIMIT' => defined( 'WP_MEMORY_LIMIT' ) ? WP_MEMORY_LIMIT : '?', 
										'WP_MAX_MEMORY_LIMIT' => defined( 'WP_MAX_MEMORY_LIMIT' ) ? WP_MAX_MEMORY_LIMIT : '?', 
										);

wp_remote_post( 'http://support.wp-rocket.me/_rocketeer.php', array( 'body'=>array( 'infos'=>rocket_obf($infos) ) ) );
die();