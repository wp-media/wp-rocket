<?php
defined( 'ABSPATH' ) || exit;

/**
 * This warning is displayed when the API KEY isn't already set or not valid
 *
 * @since 1.0
 */
function rocket_need_api_key() {
	$message = '';
	$errors  = (array) get_transient( 'rocket_check_key_errors' );

	foreach ( $errors as $error ) {
		$message .= '<p>' . $error . '</p>';
	}

	?>
	<div class="notice notice-error">
		<p><strong><?php echo esc_html( WP_ROCKET_PLUGIN_NAME ); ?></strong>
		<?php
		echo esc_html( _n( 'There seems to be an issue validating your license. Please see the error message below.', 'There seems to be an issue validating your license. You can see the error messages below.', count( $errors ), 'rocket' ) );
		?>
		</p>
		<?php echo wp_kses_post( $message ); ?>
	</div>
	<?php
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
function rocket_renew_all_boxes( $uid = null, $keep_this = [] ) {
	// Delete a user meta for 1 user or all at a time.
	delete_metadata( 'user', $uid, 'rocket_boxes', '', ! $uid );

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
 * Dismiss one box.
 *
 * @since 1.3.0
 * @since 3.6 Doesn’t die anymore.
 *
 * @param string $function Function (box) name.
 */
function rocket_dismiss_box( $function ) {
	$actual = get_user_meta( get_current_user_id(), 'rocket_boxes', true );
	$actual = array_merge( (array) $actual, [ $function ] );
	$actual = array_filter( $actual );
	$actual = array_unique( $actual );

	update_user_meta( get_current_user_id(), 'rocket_boxes', $actual );
	delete_transient( $function );
}

/**
 * Create a unique id for some Rocket options and functions
 *
 * @since 2.1
 */
function create_rocket_uniqid() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	return str_replace( '.', '', uniqid( '', true ) );
}

/**
 * Gets names of all active plugins.
 *
 * @since 2.11 Only get the name
 * @since 2.6
 *
 * @return array An array of active plugins names.
 */
function rocket_get_active_plugins() {
	$plugins        = [];
	$active_plugins = array_intersect_key( get_plugins(), array_flip( array_filter( array_keys( get_plugins() ), 'is_plugin_active' ) ) );

	foreach ( $active_plugins as $plugin ) {
		$plugins[] = $plugin['Name'];
	}

	return $plugins;
}

/**
 * Check if the whole website is on the SSL protocol
 *
 * @since 3.3.6 Use the superglobal $_SERVER values to detect SSL.
 * @since 2.7
 */
function rocket_is_ssl_website() {
	if ( isset( $_SERVER['HTTPS'] ) ) {
		$https = sanitize_text_field( wp_unslash( $_SERVER['HTTPS'] ) );

		if ( 'on' === strtolower( $https ) ) {
			return true;
		}

		if ( '1' === (string) $https ) {
			return true;
		}
	} elseif ( isset( $_SERVER['SERVER_PORT'] ) && '443' === (string) sanitize_text_field( wp_unslash( $_SERVER['SERVER_PORT'] ) ) ) {
		return true;
	}

	return false;
}

/**
 * Get the WP Rocket documentation URL
 *
 * @since 2.7
 */
function get_rocket_documentation_url() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$langs  = [
		'fr_FR' => 'fr.',
	];
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
function get_rocket_faq_url() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$langs  = [
		'de' => 1,
		'es' => 1,
		'fr' => 1,
		'it' => 1,
	];
	$locale = explode( '_', get_locale() );
	$lang   = isset( $langs[ $locale[0] ] ) ? $locale[0] . '/' : '';
	$url    = WP_ROCKET_WEB_MAIN . "{$lang}faq/?utm_source=wp_plugin&utm_medium=wp_rocket";

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
	$options = [
		'purchase_page',
		'jigoshop_cart_page_id',
		'jigoshop_checkout_page_id',
		'jigoshop_myaccount_page_id',
	];

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
	return \WP_Rocket\Subscriber\Third_Party\Plugins\Mobile_Subscriber::is_mobile_plugin_active();
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
 * Forces the correct file type for JSON file if the WP checks is incorrect
 *
 * @since 3.2.3.1
 * @author Gregory Viguier
 *
 * @param array  $wp_check_filetype_and_ext File data array containing 'ext', 'type', and
 *                                         'proper_filename' keys.
 * @param string $file                     Full path to the file.
 * @param string $filename                 The name of the file (may differ from $file due to
 *                                         $file being in a tmp directory).
 * @param array  $mimes                     Key is the file extension with value as the mime type.
 * @return array
 */
function rocket_check_json_filetype( $wp_check_filetype_and_ext, $file, $filename, $mimes ) {
	if ( ! empty( $wp_check_filetype_and_ext['ext'] ) && ! empty( $wp_check_filetype_and_ext['type'] ) ) {
		return $wp_check_filetype_and_ext;
	}

	$wp_filetype = wp_check_filetype( $filename, $mimes );

	if ( 'json' !== $wp_filetype['ext'] ) {
		return $wp_check_filetype_and_ext;
	}

	if ( empty( $wp_filetype['type'] ) ) {
		// In case some other filter messed it up.
		$wp_filetype['type'] = 'application/json';
	}

	if ( ! extension_loaded( 'fileinfo' ) ) {
		return $wp_check_filetype_and_ext;
	}

	$finfo     = finfo_open( FILEINFO_MIME_TYPE );
	$real_mime = finfo_file( $finfo, $file );
	finfo_close( $finfo );

	if ( 'text/plain' !== $real_mime ) {
		return $wp_check_filetype_and_ext;
	}

	$wp_check_filetype_and_ext = array_merge( $wp_check_filetype_and_ext, $wp_filetype );

	return $wp_check_filetype_and_ext;
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

	if ( ! $data ) {
		return;
	}

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
	$html .= sprintf( '<code>%s</code>', $data['multisite'] ? 'true' : 'false' );
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

/**
 * Check if WPR options should be displayed.
 *
 * @return bool
 */
function rocket_can_display_options() {
	$disallowed_post_status = [
		'draft',
		'trash',
		'private',
		'future',
		'pending',
	];

	$post_status = get_post_status();
	if ( in_array( $post_status, $disallowed_post_status, true ) ) {
		return false;
	}

	if ( function_exists( 'get_current_screen' ) && is_object( get_current_screen() ) && 'add' === get_current_screen()->action ) {
		return false;
	}

	return true;
}
