<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

/**
 * This warning is displayed when the API KEY isn't already set or not valid
 *
 * @since 1.0
 */
function rocket_need_api_key() {
	?>
	<div class="notice notice-warning">
		<p><strong><?php echo WP_ROCKET_PLUGIN_NAME; ?></strong>: <?php _e( 'There seems to be an issue with outgoing connections from your server. Resolve per documentation, or contact support.', 'rocket' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Add Rocket informations into USER_AGENT
 *
 * @since 1.1.0
 *
 * @param string $user_agent User Agent value.
 * @return string WP Rocket user agent
 */
function rocket_user_agent( $user_agent ) {
	$consumer_key = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_key'] ) ) {
		$consumer_key = $_POST[ WP_ROCKET_SLUG ]['consumer_key'];
	} elseif ( '' !== (string) get_rocket_option( 'consumer_key' ) ) {
		$consumer_key = (string) get_rocket_option( 'consumer_key' );
	}

	$consumer_email = '';
	if ( isset( $_POST[ WP_ROCKET_SLUG ]['consumer_email'] ) ) {
		$consumer_email = $_POST[ WP_ROCKET_SLUG ]['consumer_email'];
	} elseif ( '' !== (string) get_rocket_option( 'consumer_email' ) ) {
		$consumer_email = (string) get_rocket_option( 'consumer_email' );
	}

	$bonus       = ! get_rocket_option( 'do_beta' ) ? '' : '+';
	$php_version = preg_replace( '@^(\d\.\d+).*@', '\1', phpversion() );
	$new_ua      = sprintf( '%s;WP-Rocket|%s%s|%s|%s|%s|%s;', $user_agent, WP_ROCKET_VERSION, $bonus, $consumer_key, $consumer_email, esc_url( home_url() ), $php_version );

	return $new_ua;
}

/**
 * Renew all boxes for everyone if $uid is missing
 *
 * @since 1.1.10
 * @modified 2.1 :
 *  - Better usage of delete_user_meta into delete_metadata
 *
 * @param (int|null)     $uid : a User id, can be null, null = all users.
 * @param (string|array) $keep_this : which box have to be kept.
 * @return void
 */
function rocket_renew_all_boxes( $uid = null, $keep_this = array() ) {
	// Delete a user meta for 1 user or all at a time.
	delete_metadata( 'user', $uid, 'rocket_boxes', null === $uid );

	// $keep_this works only for the current user.
	if ( ! empty( $keep_this ) && null !== $uid ) {
		if ( ! is_array( $keep_this ) ) {
			$keep_this = (array) $keep_this;
		}

		foreach ( $keep_this as $kt ) {
			rocket_dismiss_box( $kt );
		}
	}
}

/**
 * Renew a dismissed error box admin side
 *
 * @since 1.1.10
 *
 * @param string $function function name.
 * @param int    $uid User ID.
 * @return void
 */
function rocket_renew_box( $function, $uid = 0 ) {
	global $current_user;
	$uid    = 0 === $uid ? $current_user->ID : $uid;
	$actual = get_user_meta( $uid, 'rocket_boxes', true );

	if ( $actual && false !== array_search( $function, $actual, true ) ) {
		unset( $actual[ array_search( $function, $actual, true ) ] );
		update_user_meta( $uid, 'rocket_boxes', $actual );
	}
}

/**
 * Dismissed 1 box, wrapper of rocket_dismiss_boxes()
 *
 * @since 1.3.0
 *
 * @param string $function function name.
 * @return void
 */
function rocket_dismiss_box( $function ) {
	rocket_dismiss_boxes(
		array(
			'box'      => $function,
			'_wpnonce' => wp_create_nonce( 'rocket_ignore_' . $function ),
			'action'   => 'rocket_ignore',
		)
	);
}

/**
 * Create a unique id for some Rocket options and functions
 *
 * @since 2.1
 */
function create_rocket_uniqid() {
	return str_replace( '.', '', uniqid( '', true ) );
}

/**
 * Force our user agent header when we hit our urls
 *
 * @since 2.4
 *
 * @param array  $request An array of request arguments.
 * @param string $url     Requested URL.
 * @return array An array of requested arguments
 */
function rocket_add_own_ua( $request, $url ) {
	if ( strpos( $url, 'wp-rocket.me' ) !== false ) {
		$request['user-agent'] = rocket_user_agent( $request['user-agent'] );
	}
	return $request;
}
add_filter( 'http_request_args', 'rocket_add_own_ua', 10, 3 );

/**
 * Gets names of all active plugins.
 *
 * @since 2.11 Only get the name
 * @since 2.6
 *
 * @return array An array of active plugins names.
 */
function rocket_get_active_plugins() {
	$plugins        = array();
	$active_plugins = array_intersect_key( get_plugins(), array_flip( array_filter( array_keys( get_plugins() ), 'is_plugin_active' ) ) );

	foreach ( $active_plugins as $plugin ) {
		$plugins[] = $plugin['Name'];
	}

	return $plugins;
}

/**
 * Check if the whole website is on the SSL protocol
 *
 * @since 2.7
 */
function rocket_is_ssl_website() {
	return 'https' === rocket_extract_url_component( home_url(), PHP_URL_SCHEME );
}

/**
 * Get the WP Rocket documentation URL
 *
 * @since 2.7
 */
function get_rocket_documentation_url() {
	$langs  = array(
		'fr_FR' => 'fr.',
		'it_IT' => 'it.',
		'de_DE' => 'de.',
	);
	$lang   = get_locale();
	$prefix = isset( $langs[ $lang ] ) ? $langs[ $lang ] : '';
	$url    = "https://{$prefix}docs.wp-rocket.me/?utm_source=wp_plugin&utm_medium=wp_rocket";

	return $url;
}

/**
 * Get WP Rocket FAQ URL
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return string URL in the correct language
 */
function get_rocket_faq_url() {
	$langs = array(
		'fr_FR' => 'fr.docs.wp-rocket.me/category/146-faq',
		'it_IT' => 'it.docs.wp-rocket.me/category/321-domande-frequenti',
		'de_DE' => 'de.docs.wp-rocket.me/category/285-haufig-gestellte-fragen-faq',
	);
	$lang  = get_locale();
	$faq   = isset( $langs[ $lang ] ) ? $langs[ $lang ] : 'docs.wp-rocket.me/category/65-faq';
	$url   = "https://{$faq}/?utm_source=wp_plugin&utm_medium=wp_rocket";

	return $url;
}

/**
 * Get the Activation Link for a given plugin
 *
 * @since 2.7.3
 * @author Geoffrey Crofte
 *
 * @param string $plugin the given plugin folder/file.php (e.i. "imagify/imagify.php").
 * @return string URL to activate the plugin
 */
function rocket_get_plugin_activation_link( $plugin ) {
	$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );

	return $activation_url;
}

/**
 * Check if a given plugin is installed but not necessarily activated
 * Note: get_plugins( $folder ) from WP Core doesn't work
 *
 * @since 2.7.3
 * @author Geoffrey Crofte
 *
 * @param string $plugin a plugin folder/file.php (e.i. "imagify/imagify.php").
 * @return bool True if installed, false otherwise
 */
function rocket_is_plugin_installed( $plugin ) {
	$installed_plugins = get_plugins();

	return isset( $installed_plugins[ $plugin ] );
}

/**
 * When Woocommerce, EDD, iThemes Exchange, Jigoshop & WP-Shop options are saved or deleted,
 * we update .htaccess & config file to get the right checkout page to exclude to the cache.
 *
 * @since 2.9.3 Support for SF Move Login moved to 3rd party file
 * @since 2.6 Add support with SF Move Login & WPS Hide Login to exclude login pages
 * @since 2.4
 *
 * @param array $old_value An array of previous settings values.
 * @param array $value An array of submitted settings values.
 */
function rocket_after_update_single_options( $old_value, $value ) {
	if ( $old_value !== $value ) {
		// Update .htaccess file rules.
		flush_rocket_htaccess();

		// Update config file.
		rocket_generate_config_file();
	}
}

/**
 * We need to regenerate the config file + htaccess depending on some plugins
 *
 * @since 2.9.3 Support for SF Move Login moved to 3rd party file
 * @since 2.6.5 Add support with SF Move Login & WPS Hide Login
 *
 * @param array $old_value An array of previous settings values.
 * @param array $value An array of submitted settings values.
 */
function rocket_after_update_array_options( $old_value, $value ) {
	$options = array(
		'purchase_page',
		'jigoshop_cart_page_id',
		'jigoshop_checkout_page_id',
		'jigoshop_myaccount_page_id',
	);

	foreach ( $options as $val ) {
		if ( ( ! isset( $old_value[ $val ] ) && isset( $value[ $val ] ) ) ||
			( isset( $old_value[ $val ], $value[ $val ] ) && $old_value[ $val ] !== $value[ $val ] )
		) {
			// Update .htaccess file rules.
			flush_rocket_htaccess();

			// Update config file.
			rocket_generate_config_file();
			break;
		}
	}
}

/**
 * Check if a mobile plugin is active
 *
 * @since 2.10
 * @author Remy Perona
 *
 * @return true if a mobile plugin in the list is active, false otherwise.
 **/
function rocket_is_mobile_plugin_active() {
	$mobile_plugins = array(
		'wptouch/wptouch.php',
		'wiziapp-create-your-own-native-iphone-app/wiziapp.php',
		'wordpress-mobile-pack/wordpress-mobile-pack.php',
		'wp-mobilizer/wp-mobilizer.php',
		'wp-mobile-edition/wp-mobile-edition.php',
		'device-theme-switcher/dts_controller.php',
		'wp-mobile-detect/wp-mobile-detect.php',
		'easy-social-share-buttons3/easy-social-share-buttons3.php',
	);

	foreach ( $mobile_plugins as $mobile_plugin ) {
		if ( is_plugin_active( $mobile_plugin ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Allow upload of JSON file.
 *
 * @since 2.10.7
 * @author Remy Perona
 *
 * @param array $wp_get_mime_types Array of allowed mime types.
 * @return array Updated array of allowed mime types
 */
function rocket_allow_json_mime_type( $wp_get_mime_types ) {
	$wp_get_mime_types['json'] = 'application/json';

	return $wp_get_mime_types;
}

/**
 * Lists Data collected for analytics
 *
 * @since  2.11
 * @author Caspar Hübinger
 *
 * @return string HTML list table
 */
function rocket_data_collection_preview_table() {

	$data = rocket_analytics_data();

	$html  = '<table class="wp-rocket-data-table widefat striped">';
	$html .= '<tbody>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'Server type:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', $data['web_server'] );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'PHP version number:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', $data['php_version'] );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'WordPress version number:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', $data['wordpress_version'] );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'WordPress multisite:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', var_export( $data['multisite'], true ) );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'Current theme:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', $data['current_theme'] );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'Current site language:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<code>%s</code>', $data['locale'] );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'Active plugins:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<em>%s</em>', __( 'Plugin names of all active plugins', 'rocket' ) );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '<tr>';
	$html .= '<td class="column-primary">';
	$html .= sprintf( '<strong>%s</strong>', __( 'Anonymized WP Rocket settings:', 'rocket' ) );
	$html .= '</td>';
	$html .= '<td>';
	$html .= sprintf( '<em>%s</em>', __( 'Which WP Rocket settings are active', 'rocket' ) );
	$html .= '</td>';
	$html .= '</tr>';

	$html .= '</tbody>';
	$html .= '</table>';

	return $html;
}

/**
 * Adds error message after settings import and redirects.
 *
 * @since 3.0
 * @author Remy Perona
 *
 * @param string $message Message to display in the error notice.
 * @param string $status  Status of the error.
 * @return void
 */
function rocket_settings_import_redirect( $message, $status ) {
	add_settings_error( 'general', 'settings_updated', $message, $status );

	set_transient( 'settings_errors', get_settings_errors(), 30 );

	$goback = add_query_arg( 'settings-updated', 'true', wp_get_referer() );
	wp_safe_redirect( esc_url_raw( $goback ) );
	die();
}
