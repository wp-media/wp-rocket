<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * Link to the configuration page of the plugin, support & documentation
 *
 * @since 1.0
 *
 * @param array $actions Array of links to display.
 * @return array Updated array of links
 */
function rocket_settings_action_links( $actions ) {
	if ( ! rocket_is_white_label() ) {
		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', 'http://wp-rocket.me/support/', __( 'Support', 'rocket' ) ) );

		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_rocket_documentation_url(), __( 'Docs', 'rocket' ) ) );

		array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_rocket_faq_url(), __( 'FAQ', 'rocket' ) ) );
	}

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ), __( 'Settings' ) ) );

	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( WP_ROCKET_FILE ), 'rocket_settings_action_links' );

/**
 * Add a link "Renew your licence" when ou can't do it automatically (expired licence but new version available)
 *
 * @since 2.2
 *
 * @param array  $plugin_meta An array of the plugin's metadata, including the version, author, author URI, and plugin URI.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 * @return array Updated meta content if license is expired
 */
function rocket_plugin_row_meta( $plugin_meta, $plugin_file ) {
	if ( 'wp-rocket/wp-rocket.php' === $plugin_file ) {

		$update_plugins = get_site_transient( 'update_plugins' );

		if ( false !== $update_plugins && isset( $update_plugins->response[ $plugin_file ] ) && empty( $update_plugins->response[ $plugin_file ]->package ) ) {

			$link = '<span class="dashicons dashicons-update rocket-dashicons"></span> <span class="rocket-renew">Renew your licence of WP Rocket to receive access to automatic upgrades and support.</span> <a href="http://wp-rocket.me" target="_blank" class="rocket-purchase">Purchase now</a>.';

			$plugin_meta = array_merge( (array) $link, $plugin_meta );
		}
	}

	return $plugin_meta;
}
add_action( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );

/**
 * Add a link "Purge this cache" in the post edit area
 *
 * @since 1.0
 *
 * @param array  $actions An array of row action links.
 * @param object $post The post object.
 * @return array Updated array of row action links
 */
function rocket_post_row_actions( $actions, $post ) {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}
	return $actions;
}
add_filter( 'page_row_actions', 'rocket_post_row_actions', 10, 2 );
add_filter( 'post_row_actions', 'rocket_post_row_actions', 10, 2 );

/**
 * Add a link "Purge this cache" in the taxonomy edit area
 *
 * @since 1.0
 *
 * @param array  $actions An array of row action links.
 * @param object $term The term object.
 * @return array Updated array of row action links
 */
function rocket_tag_row_actions( $actions, $term ) {
	global $taxnow;

	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=term-' . $term->term_id . '&taxonomy=' . $taxnow ), 'purge_cache_term-' . $term->term_id );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}

	return $actions;
}
add_filter( 'tag_row_actions', 'rocket_tag_row_actions', 10, 2 );

/**
 * Add a link "Purge this cache" in the user edit area
 *
 * @since 2.6.12
 * @param array  $actions An array of row action links.
 * @param object $user The user object.
 * @return array Updated array of row action links
 */
function rocket_user_row_actions( $actions, $user ) {
	/** This filter is documented in inc/admin-bar.php */
	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && get_rocket_option( 'cache_logged_user', false ) ) {
		$url = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=user-' . $user->ID ), 'purge_cache_user-' . $user->ID );
		$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );
	}

	return $actions;
}
add_filter( 'user_row_actions', 'rocket_user_row_actions', 10, 2 );

/**
 * Manage the dismissed boxes
 *
 * @since 2.4 Add a delete_transient on function name (box name)
 * @since 1.3.0 $args can replace $_GET when called internaly
 * @since 1.1.10
 *
 * @param array $args An array of query args.
 */
function rocket_dismiss_boxes( $args ) {
	$args = empty( $args ) ? $_GET : $args;

	if ( isset( $args['box'], $args['_wpnonce'] ) ) {

		if ( ! wp_verify_nonce( $args['_wpnonce'], $args['action'] . '_' . $args['box'] ) ) {
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json(
					array(
						'error' => 1,
					)
				);
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

		if ( 'admin-post.php' === $GLOBALS['pagenow'] ) {
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json(
					array(
						'error' => 0,
					)
				);
			} else {
				wp_safe_redirect( wp_get_referer() );
				die();
			}
		}
	}
}
add_action( 'wp_ajax_rocket_ignore', 'rocket_dismiss_boxes' );
add_action( 'admin_post_rocket_ignore', 'rocket_dismiss_boxes' );

/**
 * Renew the plugin modification warning on plugin de/activation
 *
 * @since 1.3.0
 *
 * @param string $plugin plugin name.
 */
function rocket_dismiss_plugin_box( $plugin ) {
	if ( plugin_basename( WP_ROCKET_FILE ) !== $plugin ) {
		rocket_renew_box( 'rocket_warning_plugin_modification' );
	}
}
add_action( 'activated_plugin', 'rocket_dismiss_plugin_box' );
add_action( 'deactivated_plugin', 'rocket_dismiss_plugin_box' );

/**
 * Display a prevention message when enabling or disabling a plugin can be in conflict with WP Rocket
 *
 * @since 1.3.0
 */
function rocket_deactivate_plugin() {
	if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'deactivate_plugin' ) ) {
		wp_nonce_ays( '' );
	}

	deactivate_plugins( $_GET['plugin'] );

	wp_safe_redirect( wp_get_referer() );
	die();
}
add_action( 'admin_post_deactivate_plugin', 'rocket_deactivate_plugin' );

/**
 * Reset White Label values to WP Rocket default values
 *
 * @since 2.1
 */
function rocket_reset_white_label_values_action() {
	if ( isset( $_GET['_wpnonce'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'rocket_resetwl' ) ) {
		rocket_reset_white_label_values( true );
	}
	wp_safe_redirect( add_query_arg( 'page', 'wprocket', remove_query_arg( 'page', wp_get_referer() ) ) );
	die();
}
add_action( 'admin_post_rocket_resetwl', 'rocket_reset_white_label_values_action' );

/**
 * White Label the plugin, if you need to
 *
 * @since 2.1
 *
 * @param array $plugins An array of plugins installed.
 * @return array Updated array of plugins installed
 */
function rocket_white_label( $plugins ) {
	$white_label_description = get_rocket_option( 'wl_description' );
	// We change the plugin's header.
	$plugins['wp-rocket/wp-rocket.php'] = array(
		'Name'          => get_rocket_option( 'wl_plugin_name' ),
		'PluginURI'     => get_rocket_option( 'wl_plugin_URI' ),
		'Version'       => isset( $plugins['wp-rocket/wp-rocket.php']['Version'] ) ? $plugins['wp-rocket/wp-rocket.php']['Version'] : '',
		'Description'   => reset( ( $white_label_description ) ),
		'Author'        => get_rocket_option( 'wl_author' ),
		'AuthorURI'     => get_rocket_option( 'wl_author_URI' ),
		'TextDomain'    => isset( $plugins['wp-rocket/wp-rocket.php']['TextDomain'] ) ? $plugins['wp-rocket/wp-rocket.php']['TextDomain'] : '',
		'DomainPath'    => isset( $plugins['wp-rocket/wp-rocket.php']['DomainPath'] ) ? $plugins['wp-rocket/wp-rocket.php']['DomainPath'] : '',
	);

	// if white label, remove our names from contributors.
	if ( rocket_is_white_label() ) {
		remove_filter( 'plugin_row_meta', 'rocket_plugin_row_meta', 10, 2 );
	}

	return $plugins;
}
add_filter( 'all_plugins', 'rocket_white_label' );

/**
 * When you're doing an update, the constant does not contain yet your option or any value, reset and redirect!
 *
 * @since 2.1
 */
function rocket_check_no_empty_name() {
	$wl_plugin_name = trim( get_rocket_option( 'wl_plugin_name' ) );

	if ( empty( $wl_plugin_name ) ) {
		rocket_reset_white_label_values( false );
		wp_safe_redirect( $_SERVER['REQUEST_URI'] );
		die();
	}
}
add_action( 'admin_init', 'rocket_check_no_empty_name', 11 );

/**
 * This function will force the direct download of the plugin's options, compressed.
 *
 * @since 2.2
 */
function rocket_do_options_export() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_export' ) ) {
		wp_nonce_ays( '' );
	}

	$filename_prefix = rocket_is_white_label() ? sanitize_title( get_rocket_option( 'wl_plugin_name' ) ) : 'wp-rocket';

	$filename = sprintf( '%s-settings-%s-%s.json', $filename_prefix, date( 'Y-m-d' ), uniqid() );
	$gz = 'gz' . strrev( 'etalfed' );
	$options = wp_json_encode( get_option( WP_ROCKET_SLUG ) ); // do not use get_rocket_option() here.
	nocache_headers();
	@header( 'Content-Type: application/json' );
	@header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	@header( 'Content-Transfer-Encoding: binary' );
	@header( 'Content-Length: ' . strlen( $options ) );
	@header( 'Connection: close' );
	echo $options;
	exit();
}
add_action( 'admin_post_rocket_export', 'rocket_do_options_export' );

/**
 * Do the rollback
 *
 * @since 2.4
 */
function rocket_rollback() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'rocket_rollback' ) ) {
		wp_nonce_ays( '' );
	}

	$plugin_transient   = get_site_transient( 'update_plugins' );
	$plugin_folder      = plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin_file        = basename( WP_ROCKET_FILE );
	$version            = WP_ROCKET_LASTVERSION;
	$c_key              = get_rocket_option( 'consumer_key' );
	$url                = sprintf( 'https://wp-rocket.me/%s/wp-rocket_%s.zip', $c_key, $version );
	$temp_array         = array(
		'slug'        => $plugin_folder,
		'new_version' => $version,
		'url'         => 'https://wp-rocket.me',
		'package'     => $url,
	);

	$temp_object = (object) $temp_array;
	$plugin_transient->response[ $plugin_folder . '/' . $plugin_file ] = $temp_object;
	set_site_transient( 'update_plugins', $plugin_transient );

	$c_key = get_rocket_option( 'consumer_key' );
	$transient = get_transient( 'rocket_warning_rollback' );

	if ( false === $transient ) {
		require_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
		// translators: %s is the plugin name.
		$title = sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
		$plugin = 'wp-rocket/wp-rocket.php';
		$nonce = 'upgrade-plugin_' . $plugin;
		$url = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
		$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
		$upgrader = new Plugin_Upgrader( $upgrader_skin );
		remove_filter( 'site_transient_update_plugins', 'rocket_check_update', 100 );
		$upgrader->upgrade( $plugin );
		wp_die(
			// translators: %s is the plugin name.
			'', sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME ), array(
				'response' => 200,
			)
		);
	}
}
add_action( 'admin_post_rocket_rollback', 'rocket_rollback' );

if ( ! defined( 'DOING_AJAX' ) && ! defined( 'DOING_AUTOSAVE' ) ) {
	add_action( 'admin_init', 'rocket_init_cache_dir' );
	add_action( 'admin_init', 'rocket_maybe_generate_advanced_cache_file' );
	add_action( 'admin_init', 'rocket_maybe_generate_config_files' );
	add_action( 'admin_init', 'rocket_maybe_set_wp_cache_define' );
}

/**
 * Regenerate the advanced-cache.php file if an issue is detected.
 *
 * @since 2.6
 */
function rocket_maybe_generate_advanced_cache_file() {
	if ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) || ( defined( 'WP_ROCKET_ADVANCED_CACHE_PROBLEM' ) && WP_ROCKET_ADVANCED_CACHE_PROBLEM ) ) {
		rocket_generate_advanced_cache_file();
	}
}

/**
 * Regenerate config file if an issue is detected.
 *
 * @since 2.6.5
 */
function rocket_maybe_generate_config_files() {
	$home = get_rocket_parse_url( home_url() );
	$path = ( ! empty( $home['path'] ) ) ? str_replace( '/', '.', untrailingslashit( $home['path'] ) ) : '';

	if ( ! file_exists( WP_ROCKET_CONFIG_PATH . strtolower( $home['host'] ) . $path . '.php' ) ) {
		rocket_generate_config_file();
	}
}

/**
 * Define WP_CACHE to true if it's not defined yet.
 *
 * @since 2.6
 */
function rocket_maybe_set_wp_cache_define() {
	if ( defined( 'WP_CACHE' ) && ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}
}

/**
 * Filter plugin fetching API results to inject Imagify
 *
 * @since 2.10.7
 * @author Remy Perona
 *
 * @param object|WP_Error $result Response object or WP_Error.
 * @param string          $action The type of information being requested from the Plugin Install API.
 * @param object          $args   Plugin API arguments.
 *
 * @return array Updated array of results
 */
function rocket_add_imagify_api_result( $result, $action, $args ) {
	if ( empty( $args->browse ) ) {
		return $result;
	}

	if ( 'featured' !== $args->browse && 'recommended' !== $args->browse && 'popular' !== $args->browse ) {
		return $result;
	}

	if ( ! isset( $result->info['page'] ) || 1 < $result->info['page'] ) {
		return $result;
	}

	if ( is_plugin_active( 'imagify/imagify.php' ) || is_plugin_active_for_network( 'imagify/imagify.php' ) ) {
		return $result;
	}

	// grab all slugs from the api results.
	$result_slugs = wp_list_pluck( $result->plugins, 'slug' );

	if ( in_array( 'imagify', $result_slugs, true ) ) {
		return $result;
	}

	$query_args = array(
		'slug'   => 'imagify',
		'fields' => array(
			'icons'             => true,
			'active_installs'   => true,
			'short_description' => true,
			'group'             => true,
		),
	);
	$imagify_data = plugins_api( 'plugin_information', $query_args );

	if ( is_wp_error( $imagify_data ) ) {
		return $result;
	}

	if ( 'featured' === $args->browse ) {
		array_push( $result->plugins, $imagify_data );
	} else {
		array_unshift( $result->plugins, $imagify_data );
	}

	return $result;
}
add_filter( 'plugins_api_result', 'rocket_add_imagify_api_result', 11, 3 );

/**
 * Gets all data to send to the analytics system
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @return array An array of data
 */
function rocket_analytics_data() {
	global $wp_version, $is_nginx, $is_apache, $is_iis7, $is_IIS;

	$untracked_wp_rocket_options = array(
		'license'                 => 1,
		'consumer_email'          => 1,
		'consumer_key'            => 1,
		'secret_key'              => 1,
		'secret_cache_key'        => 1,
		'minify_css_key'          => 1,
		'minify_js_key'           => 1,
		'sitemaps'                => 1,
		'cdn_zone'                => 1,
		'cdn_cnames'              => 1,
		'cloudflare_email'        => 1,
		'cloudflare_api_key'      => 1,
		'cloudflare_domain'       => 1,
		'cloudflare_zone_id'      => 1,
		'cloudflare_old_settings' => 1,
		'submit_optimize'         => 1,
		'analytics_enabled'       => 1,
		'wl_author'               => 1,
		'wl_author_URI'           => 1,
		'wl_description'          => 1,
		'wl_plugin_URI'           => 1,
		'wl_plugin_name'          => 1,
		'wl_plugin_slug'          => 1,
	);

	$theme = wp_get_theme();
	$data  = array_diff_key( get_option( WP_ROCKET_SLUG ), $untracked_wp_rocket_options );
	$locale = explode('_', get_locale() );

	if ( $is_nginx ) {
		$data['web_server'] = 'NGINX';
	} elseif ( $is_apache ) {
		$data['web_server'] = 'Apache';
	} elseif ( $is_iis7 ) {
		$data['web_server'] = 'IIS 7';
	} elseif ( $is_IIS ) {
		$data['web_server'] = 'IIS';
	} else {
		$data['web_server'] = 'unknown';
	}

	$data['php_version']       = preg_replace( '@^(\d\.\d+).*@', '\1', phpversion() );
	$data['wordpress_version'] = preg_replace( '@^(\d\.\d+).*@', '\1', $wp_version );
	$data['current_theme']     = $theme->get( 'Name' );
	$data['active_plugins']    = rocket_get_active_plugins();
	$data['locale']            = $locale[0];
	$data['multisite']         = is_multisite();

	return $data;
}

/**
 * Determines if we should send the analytics data
 *
 * @since 2.11
 * @author Remy Perona
 *
 * @return bool True if we should send them, false otherwise
 */
function rocket_send_analytics_data() {
	if ( ! get_rocket_option( 'analytics_enabled' ) ) {
		return false;
	}

	if ( ! current_user_can( 'administrator' ) ) {
		return false;
	}

	if ( false === get_transient( 'rocket_send_analytics_data' ) ) {
		set_transient( 'rocket_send_analytics_data', 1, 7 * DAY_IN_SECONDS );
		return true;
	}

	return false;
}

/**
 * Handles the analytics opt-in notice selection and prevent further display
 *
 * @since 2.11
 * @author Remy Perona
 */
function rocket_analytics_optin() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'analytics_optin' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'administrator' ) ) {
		wp_redirect( wp_get_referer() );
		die();
	}

	if ( 'yes' === $_GET['value'] ) {
		update_rocket_option( 'analytics_enabled', 1 );
		set_transient( 'rocket_analytics_optin', 1 );
	}

	update_option( 'rocket_analytics_notice_displayed', 1 );

	wp_redirect( wp_get_referer() );
	die();
}
add_action( 'admin_post_rocket_analytics_optin', 'rocket_analytics_optin' );
