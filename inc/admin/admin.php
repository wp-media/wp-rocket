<?php

defined( 'ABSPATH' ) || exit;

/**
 * Link to the configuration page of the plugin, support & documentation
 *
 * @since 1.0
 *
 * @param array $actions Array of links to display.
 * @return array Updated array of links
 */
function rocket_settings_action_links( $actions ) {
	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		return $actions;
	}

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', 'https://wp-rocket.me/support/?utm_source=wp_plugin&utm_medium=wp_rocket', __( 'Support', 'rocket' ) ) );

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_rocket_documentation_url(), __( 'Docs', 'rocket' ) ) );

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', get_rocket_faq_url(), __( 'FAQ', 'rocket' ) ) );

	array_unshift( $actions, sprintf( '<a href="%s">%s</a>', admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ), __( 'Settings', 'rocket' ) ) );

	return $actions;
}
add_filter( 'plugin_action_links_' . plugin_basename( WP_ROCKET_FILE ), 'rocket_settings_action_links' );

/**
 * Add a link "Renew your licence" when you can't do it automatically (expired licence but new version available)
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
	if ( ! current_user_can( 'rocket_purge_posts' ) ) {
		return $actions;
	}

	$url                     = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=post-' . $post->ID ), 'purge_cache_post-' . $post->ID );
	$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );

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

	if ( ! current_user_can( 'rocket_purge_terms' ) ) {
		return $actions;
	}

	$url                     = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=term-' . $term->term_id . '&taxonomy=' . $taxnow ), 'purge_cache_term-' . $term->term_id );
	$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );

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
	if ( ! current_user_can( 'rocket_purge_users' ) || ! get_rocket_option( 'cache_logged_user', false ) ) {
		return $actions;
	}

	$url                     = wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=user-' . $user->ID ), 'purge_cache_user-' . $user->ID );
	$actions['rocket_purge'] = sprintf( '<a href="%s">%s</a>', $url, __( 'Clear this cache', 'rocket' ) );

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
	$args = empty( $args ) ? $_GET : $args; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

	if ( isset( $args['box'], $args['_wpnonce'] ) ) {

		if ( ! wp_verify_nonce( $args['_wpnonce'], $args['action'] . '_' . $args['box'] ) ) {
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json(
					[
						'error' => 1,
					]
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
		$actual = array_merge( (array) $actual, [ $args['box'] ] );
		$actual = array_filter( $actual );
		$actual = array_unique( $actual );
		update_user_meta( $current_user->ID, 'rocket_boxes', $actual );
		delete_transient( $args['box'] );

		if ( 'admin-post.php' === $GLOBALS['pagenow'] ) {
			if ( defined( 'DOING_AJAX' ) ) {
				wp_send_json(
					[
						'error' => 0,
					]
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
	if ( ! isset( $_GET['plugin'], $_GET['_wpnonce'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'deactivate_plugin' ) ) {
		wp_nonce_ays( '' );
	}

	deactivate_plugins( sanitize_key( $_GET['plugin'] ) );

	wp_safe_redirect( wp_get_referer() );
	die();
}
add_action( 'admin_post_deactivate_plugin', 'rocket_deactivate_plugin' );

/**
 * This function will force the direct download of the plugin's options, compressed.
 *
 * @since 2.2
 */
function rocket_do_options_export() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_export' ) ) {
		wp_nonce_ays( '' );
	}

	$filename = sprintf( 'wp-rocket-settings-%s-%s.json', date( 'Y-m-d' ), uniqid() ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
	$gz       = 'gz' . strrev( 'etalfed' );
	$options  = wp_json_encode( get_option( WP_ROCKET_SLUG ) ); // do not use get_rocket_option() here.
	nocache_headers();
	@header( 'Content-Type: application/json' );
	@header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
	@header( 'Content-Transfer-Encoding: binary' );
	@header( 'Content-Length: ' . strlen( $options ) );
	@header( 'Connection: close' );
	echo $options; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	exit();
}
add_action( 'admin_post_rocket_export', 'rocket_do_options_export' );

/**
 * Do the rollback
 *
 * @since 2.4
 */
function rocket_rollback() {
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_rollback' ) ) {
		wp_nonce_ays( '' );
	}

	$plugin_transient = get_site_transient( 'update_plugins' );
	$plugin_folder    = plugin_basename( dirname( WP_ROCKET_FILE ) );
	$plugin           = $plugin_folder . '/' . basename( WP_ROCKET_FILE );

	$plugin_transient->response[ $plugin ] = (object) [
		'slug'        => $plugin_folder,
		'new_version' => WP_ROCKET_LASTVERSION,
		'url'         => 'https://wp-rocket.me',
		'package'     => sprintf( 'https://wp-rocket.me/%s/wp-rocket_%s.zip', get_rocket_option( 'consumer_key' ), WP_ROCKET_LASTVERSION ),
	];

	set_site_transient( 'update_plugins', $plugin_transient );

	require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

	// translators: %s is the plugin name.
	$title         = sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME );
	$nonce         = 'upgrade-plugin_' . $plugin;
	$url           = 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $plugin );
	$upgrader_skin = new Plugin_Upgrader_Skin( compact( 'title', 'nonce', 'url', 'plugin' ) );
	$upgrader      = new Plugin_Upgrader( $upgrader_skin );

	remove_filter( 'site_transient_update_plugins', 'rocket_check_update', 1 );
	add_filter( 'update_plugin_complete_actions', 'rocket_rollback_add_return_link' );
	rocket_put_content( WP_CONTENT_DIR . '/advanced-cache.php', '' );

	$upgrader->upgrade( $plugin );

	wp_die(
		'',
		// translators: %s is the plugin name.
		esc_html( sprintf( __( '%s Update Rollback', 'rocket' ), WP_ROCKET_PLUGIN_NAME ) ),
		[
			'response' => 200,
		]
	);
}
add_action( 'admin_post_rocket_rollback', 'rocket_rollback' );

/**
 * After a rollback has been done, replace the "return to" link by a link pointing to WP Rocket's tools page.
 * A link to the plugins page is kept in case the plugin is not reactivated correctly.
 *
 * @since  3.2.4
 * @author Grégory Viguier
 * @author Arun Basil Lal
 *
 * @param  array $update_actions Array of plugin action links.
 * @return array                 The array of links where the "return to" link has been replaced.
 */
function rocket_rollback_add_return_link( $update_actions ) {
	if ( ! isset( $update_actions['plugins_page'] ) ) {
		return $update_actions;
	}

	$update_actions['plugins_page'] = sprintf(
		/* translators: 1 and 3 are link openings, 2 is a link closing. */
		__( '%1$sReturn to WP Rocket%2$s or %3$sgo to Plugins page%2$s', 'rocket' ),
		'<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) . '#tools' ) . '" target="_parent">',
		'</a>',
		'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '" target="_parent">'
	);

	return $update_actions;
}

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
	$home = get_rocket_parse_url( rocket_get_home_url() );

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

	$query_args   = [
		'slug'   => 'imagify',
		'fields' => [
			'icons'             => true,
			'active_installs'   => true,
			'short_description' => true,
			'group'             => true,
		],
	];
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
 * @since 3.0 Send CDN zones, sitemaps paths, and count the number of CDN URLs used
 * @since 2.11
 * @author Remy Perona
 *
 * @return mixed An array of data, or false if WP Rocket options is not an array
 */
function rocket_analytics_data() {
	global $wp_version, $is_nginx, $is_apache, $is_iis7, $is_IIS;

	if ( ! is_array( get_option( WP_ROCKET_SLUG ) ) ) {
		return false;
	}

	$untracked_wp_rocket_options = [
		'license'                 => 1,
		'consumer_email'          => 1,
		'consumer_key'            => 1,
		'secret_key'              => 1,
		'secret_cache_key'        => 1,
		'minify_css_key'          => 1,
		'minify_js_key'           => 1,
		'cloudflare_email'        => 1,
		'cloudflare_api_key'      => 1,
		'cloudflare_zone_id'      => 1,
		'cloudflare_old_settings' => 1,
		'submit_optimize'         => 1,
		'analytics_enabled'       => 1,
	];

	$theme              = wp_get_theme();
	$data               = array_diff_key( get_option( WP_ROCKET_SLUG ), $untracked_wp_rocket_options );
	$locale             = explode( '_', get_locale() );
	$data['web_server'] = 'Unknown';

	if ( $is_nginx ) {
		$data['web_server'] = 'NGINX';
	} elseif ( $is_apache ) {
		$data['web_server'] = 'Apache';
	} elseif ( $is_iis7 ) {
		$data['web_server'] = 'IIS 7';
	} elseif ( $is_IIS ) {
		$data['web_server'] = 'IIS';
	}

	$data['php_version']       = preg_replace( '@^(\d\.\d+).*@', '\1', phpversion() );
	$data['wordpress_version'] = preg_replace( '@^(\d\.\d+).*@', '\1', $wp_version );
	$data['current_theme']     = $theme->get( 'Name' );
	$data['active_plugins']    = rocket_get_active_plugins();
	$data['locale']            = $locale[0];
	$data['multisite']         = is_multisite();
	$data['cdn_cnames']        = count( $data['cdn_cnames'] );
	$data['sitemaps']          = array_map( 'rocket_clean_exclude_file', $data['sitemaps'] );

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
	if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'analytics_optin' ) ) {
		wp_nonce_ays( '' );
	}

	if ( ! current_user_can( 'administrator' ) ) {
		wp_safe_redirect( wp_get_referer() );
		die();
	}

	if ( isset( $_GET['value'] ) && 'yes' === $_GET['value'] ) {
		update_rocket_option( 'analytics_enabled', 1 );
		set_transient( 'rocket_analytics_optin', 1 );
	}

	update_option( 'rocket_analytics_notice_displayed', 1 );

	wp_safe_redirect( wp_get_referer() );
	die();
}
add_action( 'admin_post_rocket_analytics_optin', 'rocket_analytics_optin' );

/**
 * Handle WP Rocket settings import.
 *
 * @since 3.0 Hooked on admin_post now
 * @since 2.10.7
 * @author Remy Perona
 *
 * @return void
 */
function rocket_handle_settings_import() {
	check_ajax_referer( 'rocket_import_settings', 'rocket_import_settings_nonce' );

	if ( ! current_user_can( 'rocket_manage_options' ) ) {
		rocket_settings_import_redirect( __( 'Settings import failed: you do not have the permissions to do this.', 'rocket' ), 'error' );
	}

	if ( ! isset( $_FILES['import'] ) || ( isset( $_FILES['import']['size'] ) && 0 === $_FILES['import']['size'] ) ) {
		rocket_settings_import_redirect( __( 'Settings import failed: no file uploaded.', 'rocket' ), 'error' );
	}

	if ( isset( $_FILES['import']['name'] ) && ! preg_match( '/wp-rocket-settings-20\d{2}-\d{2}-\d{2}-[a-f0-9]{13}\.(?:txt|json)/', sanitize_file_name( $_FILES['import']['name'] ) ) ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		rocket_settings_import_redirect( __( 'Settings import failed: incorrect filename.', 'rocket' ), 'error' );
	}

	add_filter( 'mime_types', 'rocket_allow_json_mime_type' );
	add_filter( 'wp_check_filetype_and_ext', 'rocket_check_json_filetype', 10, 4 );

	$mimes     = get_allowed_mime_types();
	$mimes     = rocket_allow_json_mime_type( $mimes );
	$file_data = wp_check_filetype_and_ext( $_FILES['import']['tmp_name'], sanitize_file_name( $_FILES['import']['name'] ), $mimes ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash, WordPress.Security.ValidatedSanitizedInput.InputNotValidated

	if ( 'text/plain' !== $file_data['type'] && 'application/json' !== $file_data['type'] ) {
		rocket_settings_import_redirect( __( 'Settings import failed: incorrect filetype.', 'rocket' ), 'error' );
	}

	$_post_action       = isset( $_POST['action'] ) ? wp_unslash( sanitize_key( $_POST['action'] ) ) : '';
	$_POST['action']    = 'wp_handle_sideload';
	$overrides          = [];
	$overrides['mimes'] = $mimes;
	$file               = wp_handle_sideload( $_FILES['import'], $overrides ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.MissingUnslash

	if ( isset( $file['error'] ) ) {
		rocket_settings_import_redirect( __( 'Settings import failed: ', 'rocket' ) . $file['error'], 'error' );
	}

	$_POST['action'] = $_post_action;
	$settings        = rocket_direct_filesystem()->get_contents( $file['file'] );
	remove_filter( 'mime_types', 'rocket_allow_json_mime_type' );
	remove_filter( 'wp_check_filetype_and_ext', 'rocket_check_json_filetype', 10 );

	if ( 'text/plain' === $file_data['type'] ) {
		$gz       = 'gz' . strrev( 'etalfni' );
		$settings = $gz( $settings );
		$settings = maybe_unserialize( $settings );
	} elseif ( 'application/json' === $file_data['type'] ) {
		$settings = json_decode( $settings, true );

		if ( null === $settings ) {
			rocket_settings_import_redirect( __( 'Settings import failed: unexpected file content.', 'rocket' ), 'error' );
		}
	}

	rocket_put_content( $file['file'], '' );
	rocket_direct_filesystem()->delete( $file['file'] );

	if ( is_array( $settings ) ) {
		$options_api     = new WP_Rocket\Admin\Options( 'wp_rocket_' );
		$current_options = $options_api->get( 'settings', [] );

		$settings['consumer_key']     = $current_options['consumer_key'];
		$settings['consumer_email']   = $current_options['consumer_email'];
		$settings['secret_key']       = $current_options['secret_key'];
		$settings['secret_cache_key'] = $current_options['secret_cache_key'];
		$settings['minify_css_key']   = $current_options['minify_css_key'];
		$settings['minify_js_key']    = $current_options['minify_js_key'];
		$settings['version']          = $current_options['version'];

		$options_api->set( 'settings', $settings );

		rocket_settings_import_redirect( __( 'Settings imported and saved.', 'rocket' ), 'updated' );
	}
}
add_action( 'admin_post_rocket_import_settings', 'rocket_handle_settings_import' );
