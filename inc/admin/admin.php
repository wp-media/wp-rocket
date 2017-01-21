<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Link to the configuration page of the plugin
 *
 * @since 1.0
 */
add_filter( 'plugin_action_links_' . plugin_basename( WP_ROCKET_FILE ), '__rocket_settings_action_links' );
function __rocket_settings_action_links( $actions ) {
	if ( ! rocket_is_white_label() ) {
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', 'http://wp-rocket.me/support/', __( 'Support', 'rocket' ) ) );

		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_rocket_documentation_url(), __( 'Docs', 'rocket' ) ) );
	}

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ), __( 'Settings' ) ) );

    return $actions;
}

/**
 * Add a link "Renew your licence" when ou can't do it automatically (expired licence but new version available)
 *
 * @since 2.2
 *
 */
add_action( 'plugin_row_meta', '__rocket_plugin_row_meta', 10, 3 );
function __rocket_plugin_row_meta( $plugin_meta, $plugin_file, $plugin_data ) {
	if ( 'wp-rocket/wp-rocket.php' == $plugin_file ) {

		$update_plugins = get_site_transient( 'update_plugins' );

		if ( $update_plugins != false && isset( $update_plugins->response[ $plugin_file ] ) && empty( $update_plugins->response[ $plugin_file ]->package ) ) {

			$link =	'<span class="dashicons dashicons-update rocket-dashicons"></span> ' .
					'<span class="rocket-renew">Renew your licence of WP Rocket to receive access to automatic upgrades and support.</span> ' .
					'<a href="http://wp-rocket.me" target="_blank" class="rocket-purchase">Purchase now</a>.';

			$plugin_meta = array_merge( (array) $link, $plugin_meta );
		}
	}

	return $plugin_meta;
}

/**
 * Add a link "Purge this cache" in the post edit area
 *
 * @since 1.0
 * @todo manage all CPTs
 */
add_filter( 'page_row_actions', '__rocket_post_row_actions', 10, 2 );
add_filter( 'post_row_actions', '__rocket_post_row_actions', 10, 2 );
function __rocket_post_row_actions( $actions, $post ) {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}
    return $actions;
}

/**
 * Add a link "Purge this cache" in the taxonomy edit area
 *
 * @since 1.0
 * @todo manage all CPTs
 */
add_filter( 'tag_row_actions', '__rocket_tag_row_actions', 10, 2 );
function __rocket_tag_row_actions( $actions, $term ) {		
	global $taxnow;
	
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=term-' . $term->term_id  . '&taxonomy=' . $taxnow ), 'purge_cache_term-' . $term->term_id );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}
    
    return $actions;
}

/**
 * Add a link "Purge this cache" in the user edit area
 *
 * @since 2.6.12
 */
add_filter( 'user_row_actions', '__rocket_user_row_actions', 10, 2 );
function __rocket_user_row_actions( $actions, $user ) {			
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && get_rocket_option( 'cache_logged_user', false ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=user-' . $user->ID ), 'purge_cache_user-' . $user->ID );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}
    
    return $actions;
}

/**
 * Manage the dismissed boxes
 *
 * @since 2.4 Add a delete_transient on function name (box name)
 * @since 1.3.0 $args can replace $_GET when called internaly
 * @since 1.1.10
 */
add_action( 'wp_ajax_rocket_ignore', 'rocket_dismiss_boxes' );
add_action( 'admin_post_rocket_ignore', 'rocket_dismiss_boxes' );
function rocket_dismiss_boxes( $args ) {
	$args = empty( $args ) ? $_GET : $args;

	if ( isset( $args['box'], $args['_wpnonce'] ) ) {

		if ( ! wp_verify_nonce( $args['_wpnonce'], $args['action'] . '_' . $args['box'] ) ) {
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json( array( 'error' => 1 ) );
			} else {
				wp_nonce_ays( '' );
			}
		}

		if ( '__rocket_imagify_notice' === $args['box'] ) {
			update_option( 'wp_rocket_dismiss_imagify_notice', 0 );
		}
		
		global $current_user;
		$actual = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		$actual = array_merge( (array) $actual, array( $args['box'] ) );
		$actual = array_filter( $actual );
		$actual = array_unique( $actual );
		update_user_meta( $current_user->ID, 'rocket_boxes', $actual );
		delete_transient( $args['box'] );

		if ( 'admin-post.php' == $GLOBALS['pagenow'] ){
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json( array( 'error' => 0 ) );
			} else {
				wp_safe_redirect( wp_get_referer() );
				die();
			}
		}
	}
}

/**
 * Renew the plugin modification warning on plugin de/activation
 *
 * @since 1.3.0
 */
add_action( 'activated_plugin', 'rocket_dismiss_plugin_box' );
add_action( 'deactivated_plugin', 'rocket_dismiss_plugin_box' );
function rocket_dismiss_plugin_box( $plugin ) {
	if ( $plugin != plugin_basename( WP_ROCKET_FILE ) ) {
		rocket_renew_box( 'rocket_warning_plugin_modification' );
	}
}

/**
 * Display a prevention message when enabling or disabling a plugin can be in conflict with WP Rocket
 *
 * @since 1.3.0
 */
add_action( 'admin_post_deactivate_plugin', '__rocket_deactivate_plugin' );
function __rocket_deactivate_plugin() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'deactivate_plugin' ) ) {
		wp_nonce_ays( '' );
	}

	deactivate_plugins( $_GET['plugin'] );

	wp_safe_redirect( wp_get_referer() );
	die();
}

/**
 * Reset White Label values to WP Rocket default values
 *
 * @since 2.1
 */
add_action( 'admin_post_rocket_resetwl', '__rocket_reset_white_label_values_action' );
function __rocket_reset_white_label_values_action() {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'rocket_resetwl' ) ) {
		rocket_reset_white_label_values( true );
	}
	wp_safe_redirect( add_query_arg( 'page', 'wprocket', remove_query_arg( 'page', wp_get_referer() ) ) );
	die();
}

/**
 * White Label the plugin, if you need to
 *
 * @since 2.1
 *
 */
add_filter( 'all_plugins', '__rocket_white_label' );
function __rocket_white_label( $plugins ) {
	$white_label_description = get_rocket_option( 'wl_description' );
	// We change the plugin's header
	$plugins['wp-rocket/wp-rocket.php'] = array(
			'Name'			=> get_rocket_option( 'wl_plugin_name' ),
			'PluginURI'		=> get_rocket_option( 'wl_plugin_URI' ),
			'Version'		=> isset( $plugins['wp-rocket/wp-rocket.php']['Version'] ) ? $plugins['wp-rocket/wp-rocket.php']['Version'] : '',
			'Description'	=> reset( ( $white_label_description ) ),
			'Author'		=> get_rocket_option( 'wl_author' ),
			'AuthorURI'		=> get_rocket_option( 'wl_author_URI' ),
			'TextDomain'	=> isset( $plugins['wp-rocket/wp-rocket.php']['TextDomain'] ) ? $plugins['wp-rocket/wp-rocket.php']['TextDomain'] : '',
			'DomainPath'	=> isset( $plugins['wp-rocket/wp-rocket.php']['DomainPath'] ) ? $plugins['wp-rocket/wp-rocket.php']['DomainPath'] : '',
		);

	// if white label, remove our names from contributors
	if ( rocket_is_white_label() ) {
		remove_filter( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );
	}

	return $plugins;
}

/**
 * When you're doing an update, the constant does not contain yet your option or any value, reset and redirect!
 *
 * @since 2.1
 */
add_action( 'admin_init', '__rocket_check_no_empty_name', 11 );
function __rocket_check_no_empty_name() {
	$wl_plugin_name = trim( get_rocket_option( 'wl_plugin_name' ) );
	
	if ( empty( $wl_plugin_name ) ) {
		rocket_reset_white_label_values( false );
		wp_safe_redirect( $_SERVER['REQUEST_URI'] );
		die();
	}
}

/**
 * This function will force the direct download of the plugin's options, compressed.
 *
 * @since 2.2
 */
add_action( 'admin_post_rocket_export', '__rocket_do_options_export' );
function __rocket_do_options_export() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_export' ) ) {
		wp_nonce_ays( '' );
	}

    $filename_prefix = rocket_is_white_label() ? sanitize_title( get_rocket_option( 'wl_plugin_name' ) ) : 'wp-rocket';

	$filename = sprintf( '%s-settings-%s-%s.txt', $filename_prefix, date( 'Y-m-d' ), uniqid() );
	$gz = 'gz' . strrev( 'etalfed' );
	$options = $gz//;
	( serialize( get_option( WP_ROCKET_SLUG ) ), 1 ); // do not use get_rocket_option() here
	nocache_headers();
	@header( 'Content-Type: text/plain' );
	@header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	@header( 'Content-Transfer-Encoding: binary' );
	@header( 'Content-Length: ' . strlen( $options ) );
	@header( 'Connection: close' );
	echo $options;
	exit();
}

/**
 * Do the rollback
 *
 * @since 2.4
 */
add_action( 'admin_post_rocket_rollback', '__rocket_rollback' );
function __rocket_rollback() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_rollback' ) ) {
		wp_nonce_ays( '' );
	}

	$plugin_transient 	= get_site_transient( 'update_plugins' );
	$plugin_folder    	= plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin_file      	= basename( WP_ROCKET_FILE );
	$version          	= WP_ROCKET_LASTVERSION;
	$c_key 				= get_rocket_option( 'consumer_key' );
	$url 				= sprintf( 'https://wp-rocket.me/%s/wp-rocket_%s.zip', $c_key, $version );
	$temp_array 		= array(
		'slug'        => $plugin_folder,
		'new_version' => $version,
		'url'         => 'https://wp-rocket.me',
		'package'     => $url
	);

	$temp_object = (object) $temp_array;
	$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
	set_site_transient( 'update_plugins', $plugin_transient );

	$c_key = get_rocket_option( 'consumer_key' );
	$transient = get_transient( 'rocket_warning_rollback' );

	if ( false == $transient )	{
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		$title = sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
		$plugin = 'wp-rocket/wp-rocket.php';
		$nonce = 'upgrade-plugin_' . $plugin;
		$url = 'update.php?action=upgrade-plugin&plugin=' . urlencode( $plugin );
		$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
		$upgrader = new Plugin_Upgrader( $upgrader_skin );
		remove_filter( 'site_transient_update_plugins', 'rocket_check_update', 100 );
		$upgrader->upgrade( $plugin );

		wp_die( '', sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME ), array( 'response' => 200 ) );
	}
}

/*
 * Create cache folders if not exists.
 * Regenerate the advanced-cache.php file if an issue is detected.
 * Define WP_CACHE to true if it's not defined yet.
 *
 * @since 2.6.5	Check config files issues
 * @since 2.6	Check WP_CACHE & advanced-cache.php issues
 * @since 2.5.5
 */
if ( ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_AUTOSAVE' ) ) {
    add_action( 'admin_init', 'rocket_init_cache_dir' );
    add_action( 'admin_init', '__rocket_maybe_generate_advanced_cache_file' );
    add_action( 'admin_init', '__rocket_maybe_generate_config_files' );
    add_action( 'admin_init', '__rocket_maybe_set_wp_cache_define' );
}

function __rocket_maybe_generate_advanced_cache_file() {
	if ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) || ( defined( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM' ) && WP_ROCKET_ADVANCED_CACHE_PROBLEM ) ) {
		rocket_generate_advanced_cache_file();
	}
}

function __rocket_maybe_generate_config_files() {
	list( $host, $path ) = get_rocket_parse_url( home_url() );	
	$path = ( ! empty( $path ) ) ? str_replace( '/', '.', untrailingslashit( $path ) ) : '';
			
	if( ! file_exists( WP_ROCKET_CONFIG_PATH . strtolower( $host ) . $path . '.php' ) ) {
		rocket_generate_config_file();
	}
}

function __rocket_maybe_set_wp_cache_define() {
	if( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}
}

/**
 * Launches the database optimization from admin
 *
 * @since 2.8
 * @author Remy Perona
 */
add_action( 'admin_post_rocket_optimize_database', '__rocket_optimize_database' );
function __rocket_optimize_database() {
    if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_optimize_database' ) ) {
        wp_nonce_ays( '' );
    }

    do_rocket_database_optimization();

    wp_redirect( wp_get_referer() );
    die();
}