<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Add submenu in menu "Settings"
 *
 * @since 1.0
 */
add_action( 'admin_menu', 'rocket_admin_menu' );
function rocket_admin_menu()
{
	// do not use WP_ROCKET_PLUGIN_NAME here because if the WL has just been activated, the constant is not correct yet
	$wl_plugin_name = get_rocket_option( 'wl_plugin_name' );

	// same with WP_ROCKET_PLUGIN_SLUG
	$wl_plugin_slug = sanitize_key( $wl_plugin_name );

	add_options_page( $wl_plugin_name, $wl_plugin_name, apply_filters( 'rocket_capacity', 'manage_options' ), $wl_plugin_slug, 'rocket_display_options' );
}

/**
 * Used to display fields on settings form
 *
 * @since 1.0
 */
function rocket_field( $args )
{
	if( ! is_array( reset( $args ) ) ) {
		$args = array( $args );
	}

	$full = $args;

	foreach ( $full as $args ) {
		if ( isset( $args['display'] ) && !$args['display'] ) {
			continue;
		}
		$args['label_for'] = isset( $args['label_for'] ) ? $args['label_for'] : '';
		$args['name'] 	= isset( $args['name'] ) ? $args['name'] : $args['label_for'];
		$class			= isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : sanitize_html_class( $args['name'] );
		$placeholder 	= isset( $args['placeholder'] ) ? 'placeholder="'. $args['placeholder'].'" ' : '';
		$label 			= isset( $args['label'] ) ? $args['label'] : '';
		$default		= isset( $args['default'] ) ? $args['default'] : '';
		$readonly		= isset( $args['readonly'] ) ? ' readonly="readonly"' : '';

		if( ! isset( $args['fieldset'] ) || 'start' == $args['fieldset'] ){
			echo '<fieldset class="fieldname-'.sanitize_html_class( $args['name'] ).' fieldtype-'.sanitize_html_class( $args['type'] ).'">';
		}

		switch( $args['type'] ) {
			case 'number' :
			case 'email' :
			case 'text' :

				$value = esc_attr( get_rocket_option( $args['name'], '' ) );
				if ( ! $value ){
					$value = $default;
				}
				$number_options = $args['type']=='number' ? ' min="0" class="small-text"' : '';
				$autocomplete = in_array( $args['name'], array( 'consumer_key', 'consumer_email' ) ) ? ' autocomplete="off"' : '';
				$disabled = ( 'consumer_key' == $args['name'] && defined( 'WP_ROCKET_KEY' ) ) || ( 'consumer_email' == $args['name'] && defined( 'WP_ROCKET_EMAIL' ) ) ? ' disabled="disabled"' : '';
				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input<?php echo $autocomplete . $disabled; ?> type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>

				<?php
			break;

			case 'textarea' :

				$t_temp = get_rocket_option( $args['name'], '' );
				$value = ! empty( $t_temp ) ? esc_textarea( implode( "\n" , $t_temp ) ) : '';
				if ( ! $value ){
					$value = $default;
				}
				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="50" rows="5"><?php echo $value; ?></textarea>
					</label>

				<?php
			break;

			case 'checkbox' : ?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="checkbox" id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1" <?php checked( get_rocket_option( $args['name'], 0 ), 1 ); ?>/> <?php echo $args['label']; ?>
					</label>

			<?php
			break;

			case 'select' : ?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label>	<select id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]">
							<?php foreach( $args['options'] as $val => $title) { ?>
								<option value="<?php echo $val; ?>" <?php selected( get_rocket_option( $args['name'] ), $val ); ?>><?php echo $title; ?></option>
							<?php } ?>
							</select>
					<?php echo $label; ?>
					</label>

			<?php
			break;

			case 'repeater' :

				$fields = new WP_Rocket_Repeater_Field( $args );
				$fields->render();

				break;

			case 'rocket_defered_module' :

					rocket_defered_module();

			break;

			case 'helper_description' :

				$description = isset( $args['description'] ) ? '<p class="description desc '.$class.'">'.$args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'description' );

			break;

			case 'helper_help' :

				$description = isset( $args['description'] ) ? '<p class="description help '.$class.'">'.$args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'help' );

			break;

			case 'helper_warning' :

				$description = isset( $args['description'] ) ? '<p class="description warning file-error '.$class.'"><b>'.__( 'Warning: ', 'rocket') . '</b>' . $args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'warning' );

			break;

			case 'rocket_export_form' :
				?>
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_export' ), 'rocket_export' ); ?>" id="export" class="button button-secondary rocketicon"><?php _e( 'Download options', 'rocket' ); ?></a>
				<?php
			break;

			case 'rocket_import_upload_form' :

				rocket_import_upload_form( 'rocket_importer' );

			break;
			default : 'Type manquant ou incorrect'; // ne pas traduire

		}

		if( ! isset( $args['fieldset'] ) || 'end' == $args['fieldset'] ) {
			echo '</fieldset>';
		}

	}

}

/**
 * Used to display the defered module on settings form
 *
 * @since 1.1.0
 */
function rocket_defered_module()
{ ?>
	<fieldset>
	<legend class="screen-reader-text"><span><?php _e( '<b>JS</b> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>

	<div id="rkt-drop-deferred" class="rkt-module rkt-module-drop">

		<?php
		$deferred_js_files = get_rocket_option( 'deferred_js_files' );
		$deferred_js_wait = get_rocket_option( 'deferred_js_wait' );

		if( $deferred_js_files ) {

			foreach( $deferred_js_files as $k=>$_url ) {

				$checked = isset( $deferred_js_wait[$k] ) ? checked( $deferred_js_wait[$k], '1', false ) : ''; ?>

				<p class="rkt-module-drag">
					<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
					</label>

					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
				</p>
				<!-- .rkt-module-drag -->

			<?php
			}

		} else {
			// If no files yet, use this template inside #rkt-drop-deferred
			?>

			<p class="rkt-module-drag">
				<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />

				<label>
					<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Wait until this file is loaded ?', 'rocket' ); ?>
				</label>
			</p>
			<!-- .rkt-module-drag -->

		<?php } ?>

	</div>
	<!-- .rkt-drop-deferred -->

	<?php // Clone Template ?>

	<div class="rkt-module-model hide-if-js">

		<p class="rkt-module-drag">
			<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

			<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />

			<label>
				<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][]" value="1" /> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
			</label>
			<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
		</p>
		<!-- .rkt-module-drag -->
	</div>
	<!-- .rkt-model-deferred-->

	<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add URL', 'rocket' ); ?></a></p>

<?php
}

/**
 * Used to display the CNAMES module on settings form
 *
 * @since 2.1
 */
function rocket_cnames_module()
{ ?>
		<legend class="screen-reader-text"><span><?php _e( 'Replace site\'s hostname with:', 'rocket' ); ?></span></legend>

		<div id="rkt-cnames" class="rkt-module">

			<?php

			$cnames = get_rocket_option( 'cdn_cnames' );
			$cnames_zone = get_rocket_option( 'cdn_zone' );

			if( $cnames ) {

				foreach( $cnames as $k=>$_url ) { ?>

				<p>

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][<?php echo $k; ?>]" value="<?php echo esc_attr( $_url ); ?>" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][<?php echo $k; ?>]">
							<option value="all" <?php selected( $cnames_zone[$k], 'all' ); ?>><?php _e( 'All files', 'rocket' ); ?></option>
							<option value="images" <?php selected( $cnames_zone[$k], 'images' ); ?>><?php _e( 'Images', 'rocket' ); ?></option>
							<option value="css_and_js" <?php selected( $cnames_zone[$k], 'css_and_js' ); ?>>CSS & JavaScript</option>
							<option value="js" <?php selected( $cnames_zone[$k], 'js' ); ?>>JavaScript</option>
							<option value="css" <?php selected( $cnames_zone[$k], 'css' ); ?>>CSS</option>
						</select>
					</label>
					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

				</p>

				<?php
				}

			} else {

				// If no files yet, use this template inside #rkt-cnames
				?>

				<p>

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][]">
							<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
							<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
							<option value="css_and_js">CSS & JavaScript</option>
							<option value="js">JavaScript</option>
							<option value="css">CSS</option>
						</select>
					</label>

				</p>

			<?php } ?>

		</div>

		<?php // Clone Template ?>
		<div class="rkt-module-model hide-if-js">

			<p>

				<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

				<label>
					<?php _e( 'reserved for', 'rocket' ); ?>
					<select name="wp_rocket_settings[cdn_zone][]">
						<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
						<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
						<option value="css_and_js">CSS & JavaScript</option>
						<option value="js">JavaScript</option>
						<option value="css">CSS</option>
					</select>
				</label>
				<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

			</p>

		</div>

		<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add CNAME', 'rocket' ); ?></a></p>

	</fieldset>

<?php
}

/**
 * Used to display buttons on settings form, tools tab
 *
 * @since 1.1.0
 */
function rocket_button( $args )
{
	$button = $args['button'];
	$desc = isset( $args['helper_description'] ) ? $args['helper_description'] : null;
	$help = isset( $args['helper_help'] ) ? $args['helper_help'] : null;
	$warning = isset( $args['helper_warning'] ) ? $args['helper_warning'] : null;
	$class = sanitize_html_class( strip_tags( $button['button_label'] ) );
	$button_style = isset( $button['style'] ) ? 'button-'.sanitize_html_class( $button['style'] ) : 'button-secondary';

	if ( ! empty( $help ) ) {
		$help = '<p class="description help '.$class.'">'.$help['description'].'</p>';
	}
	if ( ! empty( $desc ) ) {
		$desc = '<p class="description desc '.$class.'">'.$desc['description'].'</p>';
	}
	if ( ! empty( $warning ) ) {
		$warning = '<p class="description warning file-error '.$class.'"><b>'.__( 'Warning: ', 'rocket' ) . '</b>' . $warning['description'].'</p>';
	}
?>
	<fieldset class="fieldname-<?php echo $class; ?> fieldtype-button">
		<a href="<?php echo esc_url( $button['url'] ); ?>" class="<?php echo $button_style; ?> rocketicon rocketicon-<?php echo $class; ?>"><?php echo wp_kses_post( $button['button_label'] ); ?></a>

		<?php echo apply_filters( 'rocket_help', $desc, sanitize_key( strip_tags( $button['button_label'] ) ), 'description' ); ?>
		<?php echo apply_filters( 'rocket_help', $help, sanitize_key( strip_tags( $button['button_label'] ) ), 'help' ); ?>
		<?php echo apply_filters( 'rocket_help', $warning, sanitize_key( strip_tags( $button['button_label'] ) ), 'warning' ); ?>

	</fieldset>
<?php
}

/**
 * Used to display videos buttons on settings form
 *
 * @since 2.2
 */
function rocket_video( $args )
{
	$desc = '<p class="description desc '.sanitize_html_class( $args['name'] ).'">'.$args['description'].'</p>';
?>
	<fieldset class="fieldname-<?php echo $args['name']; ?> fieldtype-button">
		<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary fancybox rocketicon rocketicon-video"><?php _e( 'Watch the video', 'rocket' ); ?></a>
		<?php echo apply_filters( 'rocket_help', $desc, $args['name'], 'description' ); ?>
	</fieldset>
<?php
}

/**
 * Used to include a file in any tab
 *
 * @since 2.2
 */
function rocket_include( $args )
{
	include_once( dirname( __FILE__ ) . '/' . str_replace( '..', '', $args['file'] ) . '.inc.php' );
}

/**
 * The main settings page construtor using the required functions from WP
 * @since 1.1.0 Add tabs, tools tab and change options severity
 * @since 1.0
 */
function rocket_display_options()
{
	// Clé API
	add_settings_section( 'rocket_display_apikey_options', __( 'License validation', 'rocket' ), '__return_false', 'apikey' );
	add_settings_field(
		'rocket_api_key',
		__( 'API Key', 'rocket' ),
		'rocket_field',
		'apikey',
		'rocket_display_apikey_options',
		array(
			array(
				'type'			=> 'text',
				'label_for'		=> 'consumer_key',
				'label_screen'	=> __( 'API Key', 'rocket' ),
			),
			array(
				'type'			=> 'helper_help',
				'name'			=> 'consumer_key',
				'description'	=> __( 'Thank you to enter the API key obtained after your purchase.', 'rocket' )
			),
		)
	);
	add_settings_field(
		'rocket_email',
		__( 'E-mail Address', 'rocket' ),
		'rocket_field',
		'apikey',
		'rocket_display_apikey_options',
		array(
			array(
				'type'         => 'email',
				'label_for'    => 'consumer_email',
				'label_screen' => __( 'E-mail Address', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'consumer_email',
				'description'  => __( 'The one used for the purchase, in your support account.', 'rocket' )
			),
		)
	);

	// Basic
	add_settings_section( 'rocket_display_main_options', __( 'Basic options', 'rocket' ), '__return_false', 'basic' );
	add_settings_field(
		'rocket_lazyload',
		__( 'Lazyload:', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => __('Enable Lazy loading images.', 'rocket' ),
				'label_for'    => 'lazyload',
				'label_screen' => __( 'Lazyload:', 'rocket' ),
			),
			array(
				'type'         => 'helper_description',
				'name'         => 'lazyload',
				'description'  => __( 'LazyLoad displays images on a page only when they are visible to the user.', 'rocket') . '<br/>' .
									  __('This reduces the number of HTTP requests mechanism and improves the loading time.', 'rocket' )
			),
		)
	);
	add_settings_field(
		'rocket_minify',
		 __( 'Files optimisation:<br/><span class="description">(Minification & Concatenation)</span>', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => 'HTML',
				'name'         => 'minify_html',
				'label_screen' => __( 'HTML Files minification', 'rocket' )
			),
			array(
				'type'         => 'checkbox',
				'label'        => 'CSS',
				'name'         => 'minify_css',
				'label_screen' => __( 'CSS Files minification', 'rocket' )
			),
			array(
				'type'		   => 'checkbox',
				'label'		   => 'JS',
				'name'		   => 'minify_js',
				'label_screen' => __( 'JS Files minification', 'rocket' ),
			),
			array(
				'type'			=> 'helper_description',
				'name'			=> 'minify',
				'description'  => __( 'Minification removes any spaces and comments present in the CSS and JavaScript files.', 'rocket' ) . '<br/>' .
									  __( 'This mechanism reduces the weight of each file and allows a faster reading of browsers and search engines.', 'rocket' ) . '<br/>' .
									  __( 'Concatenation combines all CSS and JavaScript files.', 'rocket' ) . '<br/>' .
									  __( 'This reduces the number of HTTP requests mechanism and improves the loading time.', 'rocket' )
			),
			array(
				'type'			=> 'helper_warning',
				'name'			=> 'minify_help1',
				'description'  => __( 'Concatenating files can cause display errors.', 'rocket' ),
			),
			array(
				'display'		=> ! rocket_is_white_label(),
				'type'			=> 'helper_warning',
				'name'			=> 'minify_help2',
				'description'  => sprintf( __( 'In case of any errors we recommend you to turn off this option or watch the following video: <a href="%1$s" class="fancybox">%1$s</a>.', 'rocket' ), 'http://www.youtube.com/embed/5-Llh0ivyjs' )
			),

		)
	);
	// Mobile plugins list
	$mobile_plugins = array( 	'<a href="http://wordpress.org/plugins/wptouch/" target="_blank">WP Touch</a>',
								'<a href="http://wordpress.org/plugins/wp-mobile-detector/" target="_blank">WP Mobile Detector</a>',
								'<a href="http://wordpress.org/plugins/wiziapp-create-your-own-native-iphone-app" target="_blank">wiziApp</a>',
								'<a href="http://wordpress.org/plugins/wordpress-mobile-pack/" target="_blank">WordPress Mobile Pack</a>'
								);
	add_settings_field(
		'rocket_mobile',
		__( 'Mobile cache:', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'		   => 'checkbox',
				'label'		   => __( 'Enable caching for mobile devices.', 'rocket' ),
				'label_for'	   => 'cache_mobile',
				'label_screen' => __( 'Mobile cache:', 'rocket' ),
			),
			array(
				'type'         => 'helper_warning',
				'name'         => 'mobile',
				'description'  => wp_sprintf( __( 'Don\'t turn on this option if you use one of these plugins: %l.', 'rocket' ), $mobile_plugins ),
			),
		)
	);
	add_settings_field(
		'rocket_logged_user',
		__( 'Logged in user cache:', 'rocket' ),
		'rocket_field', 'basic',
		'rocket_display_main_options',
		array(
			'type'         => 'checkbox',
			'label'        => __('Enable caching for logged in users.', 'rocket' ),
			'label_for'    => 'cache_logged_user',
			'label_screen' =>__( 'Logged in user cache:', 'rocket' ),
		)
	);
	add_settings_field(
		'rocket_ssl',
		__( 'SSL cache:', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			'type'         => 'checkbox',
			'label'        => __('Enable caching for pages with SSL protocol (<code>https://</code>).', 'rocket' ),
			'label_for'    => 'cache_ssl',
			'label_screen' => __( 'SSL cache:', 'rocket' ),
		)
	);
	add_settings_field(
		'rocket_purge',
		__( 'Clear cache delay', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'number',
				'label_for'    => 'purge_cron_interval',
				'label_screen' => __( 'Clear cache delay', 'rocket' ),
				'fieldset'     => 'start'
			),
			array(
				'type'		   => 'select',
				'label_for'	   => 'purge_cron_unit',
				'label_screen' => __( 'Unit of time', 'rocket' ),
				'fieldset'	   => 'end',
				'options' => array(
								'SECOND_IN_SECONDS' => __( 'second(s)', 'rocket' ),
								'MINUTE_IN_SECONDS' => __( 'minute(s)', 'rocket' ),
								'HOUR_IN_SECONDS'   => __( 'hour(s)', 'rocket' ),
								'DAY_IN_SECONDS'    => __( 'day(s)', 'rocket' )
							)
				),
			array(
				'type'         => 'helper_description',
				'name'         => 'purge',
				'description'  => __( 'By default, clear cache time is 24 hours, this means that once created, the cache files are automatically removed after 24 hours before being recreated.', 'rocket' ). '<br/>' .
									  __('This can be useful if you display your latest tweets or rss feeds in your sidebar, for example.', 'rocket' ),
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'purge',
				'description'  => __( 'Specify 0 for unlimited lifetime.', 'rocket' ),
				),
			)
	);

	// Advanced
	add_settings_section( 'rocket_display_imp_options', __( 'Advanced options', 'rocket' ), '__return_false', 'advanced' );
	add_settings_field(
		'rocket_dns_prefetch',
		__( 'Prefetch DNS requests:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'dns_prefetch',
				'label_screen' => __('Prefetch DNS requests', 'rocket' ),
			),
			array(
				'type'         => 'helper_description',
				'name'         => 'dns_prefetch',
				'description'  => __( 'DNS prefetching is a way for browsers to anticipate the DNS resolution of external domains from your site.', 'rocket' ) . '<br/>' .
									  __( 'This mechanism reduces the latency of some external files.', 'rocket' ),
				),
			array(
				'display'      => ! rocket_is_white_label(),
				'type'         => 'helper_help',
				'name'         => 'dns_prefetch',
				'description'  => sprintf( __( 'To learn more about this option and how to use it correctly, we advise you to watch the following video: <a href="%1$s" class="fancybox">%1$s</a>.', 'rocket' ), 'http://www.youtube.com/embed/ElJCtUidLwc' ),
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'dns_prefetch',
				'description'  => __( '<strong>NB:</strong> Enter the domain names without their protocol, for example: <code>//ajax.googleapis.com</code> without <code>http:</code> (one per line).', 'rocket' ),
				),
		)
	);
	add_settings_field(
		'rocket_purge_pages',
		__( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'cache_purge_pages',
				'label_screen' => __( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'purge_pages',
				'description'  => __( 'Enter the URL of additionnal pages to purge when updating a post (one per line).', 'rocket' ) . '<br/>' .
									  __( 'It\'s possible to use regular expressions (regex).', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'purge_pages',
				'description'  => __( '<strong>NB:</strong> When you update a post or when a comment is posted, the homepage, categories, and tags associated with this post are automatically removed from the cache and then, recreated by our bot.', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_reject_uri',
		__( 'Never cache the following pages:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'cache_reject_uri',
				'label_screen' => __( 'Never cache the following pages:', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'reject_uri',
				'description'  => __( 'Enter the URL of pages to reject (one per line).', 'rocket' ) . '<br/>' .
									  __( 'You can use regular expressions (regex).', 'rocket' )
			),
		)
	);
	add_settings_field(
		'rocket_reject_cookies',
		__( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'cache_reject_cookies',
				'label_screen' => __( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'reject_cookies',
				'description'  => __( 'List the names of the cookies (one per line).', 'rocket' )
				),
		)
	);
	add_settings_field(
		'rocket_exclude_css',
		__( '<b>CSS</b> files to exclude of the minification:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'exclude_css',
				'label_screen' => __( '<b>CSS</b> files to exclude of the minification:', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'exclude_css',
				'description'  => __( 'Specify the URL of <b>CSS</b> files to reject (one per line).', 'rocket' )
				),
		)
	);
	add_settings_field(
		'rocket_exclude_js',
		__( '<b>JS</b> files to exclude of the minification:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'textarea',
				'label_for'    => 'exclude_js',
				'label_screen' => __( '<b>JS</b> files to exclude of the minification:', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'exclude_js',
				'description'  => __( 'Specify the URL of <b>JS</b> files to reject (one per line).', 'rocket' )
				),
		)
	);
	add_settings_field(
		'minify_js_in_footer',
		__( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'                     => 'repeater',
				'label_screen'             => __( '<b>JS</b> files to be included in the footer during the minification process:', 'rocket' ),
				'name'                     => 'minify_js_in_footer',
				'placeholder'              => 'http://',
				'repeater_drag_n_drop'     => true,
				'repeater_label_add_field' => __( 'Add URL', 'rocket' )
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'minify_js_in_footer',
				'description'  => __( 'Empty the field to remove it.', 'rocket' ),
				'class'	       => 'hide-if-js'
			),
			array(
				'type'         => 'helper_warning',
				'name'         => 'minify_js_in_footer',
				'description'  => __( 'You must specify the complete URL of the files.', 'rocket' )
			)
		)
	);
	add_settings_field(
		'rocket_deferred_js',
		__( '<b>JS</b> files with deferred loading:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'rocket_defered_module',
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  => __( 'You can add JavaScript files that will be loaded asynchronously at the same time as the page loads.', 'rocket' )
				),
			array(
				'type'         => 'helper_help',
				'name'         => 'deferred_js',
				'description'  => __( 'Empty the field to remove it.', 'rocket' ),
				'class'	       => 'hide-if-js'
				),
			array(
				'type'         => 'helper_warning',
				'name'         => 'deferred_js',
				'description'  => __( 'You must specify the complete URL of the files.', 'rocket' )
				),
		)
	);

	// Content Delivery Network
	add_settings_section( 'rocket_display_cdn_options', __( 'Content Delivery Network options', 'rocket' ), '__return_false', 'cdn' );
	add_settings_field(
		'rocket_cdn',
		__( 'CDN:', 'rocket' ),
		'rocket_field',
		'cdn',
		'rocket_display_cdn_options',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => __('Enable Content Delivery Network.', 'rocket' ),
				'label_for'    => 'cdn',
				'label_screen' => __( 'CDN:', 'rocket' )
			),
			array(
				'type' 		  => 'helper_description',
				'name' 		  => 'cdn',
				'description' => __( 'CDN function replaces all URLs of your static files and media (CSS, JS, Images) with the url entered below. This way all your content will be copied to a dedicated hosting or a CDN system <a href="http://www.maxcdn.com/" target="_blank">maxCDN</a>.', 'rocket' )
			)
		)
	);
	add_settings_field(
		'rocket_cdn_cnames',
		__( 'Replace site\'s hostname with:', 'rocket' ),
		'rocket_cnames_module',
		'cdn',
		'rocket_display_cdn_options'
	);

	add_settings_section( 'rocket_display_white_label', __( 'White Label', 'rocket' ), '__return_false', 'white_label' );
	add_settings_field(
		'rocket_wl_plugin_name',
		__( 'Plugin Name:', 'rocket' ),
		'rocket_field',
		'white_label',
		'rocket_display_white_label',
		array(
			array(
				'type'         => 'text',
				'name'         => 'wl_plugin_name',
				'label_for'    => 'wl_plugin_name',
				'label_screen' => __( 'Plugin Name:', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_wl_plugin_URI',
		__( 'Plugin URI:', 'rocket' ),
		'rocket_field',
		'white_label',
		'rocket_display_white_label',
		array(
			array(
				'type'         => 'text',
				'name'         => 'wl_plugin_URI',
				'label_for'    => 'wl_plugin_URI',
				'label_screen' => __( 'Plugin URI:', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_wl_description',
		__( 'Description:', 'rocket' ),
		'rocket_field',
		'white_label',
		'rocket_display_white_label',
		array(
			array(
				'type'         => 'textarea',
				'name'         => 'wl_description',
				'label_for'    => 'wl_description',
				'label_screen' => __( 'Description:', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_wl_author',
		__( 'Author:', 'rocket' ),
		'rocket_field',
		'white_label',
		'rocket_display_white_label',
		array(
			array(
				'type'         => 'text',
				'name'         => 'wl_author',
				'label_for'    => 'wl_author',
				'label_screen' => __( 'Author:', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_wl_author_URI',
		__( 'Author URI:', 'rocket' ),
		'rocket_field',
		'white_label',
		'rocket_display_white_label',
		array(
			array(
				'type'         => 'text',
				'name'         => 'wl_author_URI',
				'label_for'    => 'wl_author_URI',
				'label_screen' => __( 'Author URI:', 'rocket' ),
			),
		)
	);
	add_settings_field(
		'rocket_wl_warning',
		'',
		'rocket_button',
		'white_label',
		'rocket_display_white_label',
		array(
	        'button'=>array(
	        	'button_label' => __( 'Reset White Label values to default', 'rocket' ),
	        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocket_resetwl' ), 'rocket_resetwl' ),
	        ),
			'helper_warning'=>array(
				'name'         => 'wl_warning',
				'description'  => __( 'If you change anything, the tutorial + FAQ + Support tabs will be hidden.', 'rocket' ),
			),
		)
	);
	// Tools
	add_settings_section( 'rocket_display_tools', __( 'Tools', 'rocket' ), '__return_false', 'tools' );
    add_settings_field(
		'rocket_do_beta',
		__( 'Beta Tester', 'rocket' ),
		'rocket_field',
		'tools',
		'rocket_display_tools',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => __( 'Yes i want!', 'rocket' ),
				'label_for'    => 'do_beta',
				'label_screen' => __( 'Beta Tester', 'rocket' )
			),
			array(
				'type' 		  => 'helper_description',
				'name' 		  => 'do_beta',
				'description' => __( 'Check it to participate to the WP Rocket Beta Program and get the new versions earlier, thanks in advance.', 'rocket' )
			)
		)
    );

	add_settings_field(
		'rocket_purge_all',
		__( 'Clear cache', 'rocket' ),
		'rocket_button',
		'tools',
		'rocket_display_tools',
		array(
			'button'=>array(
				'button_label' => __( 'Clear cache', 'rocket' ),
				'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ),
			),
			'helper_description'=>array(
				'name'         => 'purge_all',
				'description'  => __( 'Clear the cache for the whole site.', 'rocket' )
			),
		)
	);
	add_settings_field(
		'rocket_preload',
		__( 'Preload cache', 'rocket' ),
		'rocket_button',
		'tools',
		'rocket_display_tools',
		array(
	        'button'=>array(
	        	'button_label' => __( 'Preload cache', 'rocket' ),
	        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=preload' ), 'preload' ),
	        ),
			'helper_description'=>array(
				'name'         => 'preload',
	        	'description'  => __( 'Allows you to request a bot crawl to preload the cache (homepage and its internal links).', 'rocket' )
			),
		)
    );

    add_settings_field(
		'rocket_export_options',
		__( 'Settings Exporter', 'rocket' ),
		'rocket_field',
		'tools',
		'rocket_display_tools',
		array( 'type'=>'rocket_export_form', 'name'=>'export' )

    );

    add_settings_field(
		'rocket_import_options',
		__( 'Settings Importer', 'rocket' ),
		'rocket_field',
		'tools',
		'rocket_display_tools',
		array( 'type'=>'rocket_import_upload_form' )

    );

	add_settings_section( 'rocket_display_tutorials', __( 'Tutorials', 'rocket' ), '__return_false', 'tutorials' );
	add_settings_field(
		'tuto_preload_cache',
		__( 'Preload cache', 'rocket' ),
		'rocket_video',
		'tutorials',
		'rocket_display_tutorials',
		array(
			'description'	=> __( 'This video gives some explanations about our two crawler robots. They generate several cache files in a few seconds.', 'rocket' ),
			'url'			=> 'http://www.youtube.com/embed/9jDcg2f-9yM',
			'name'			=> 'tuto_preload_cache',
		)
	);
	add_settings_field(
		'css_javascript_minification',
		__( 'CSS and JavaScript minification', 'rocket' ),
		'rocket_video',
		'tutorials',
		'rocket_display_tutorials',
		array(
			'description'	=> __( 'This video gives some explanations about how to use the advanced process of minification and concatenation of CSS and JavaScript files.', 'rocket' ),
			'url'			=> 'http://www.youtube.com/embed/iziXSvZgxLk',
			'name'			=> 'css_javascript_minification',
		)
	);
	add_settings_field(
		'tuto_preload_dns_queries',
		__( 'Preloading DNS queries', 'rocket' ),
		'rocket_video',
		'tutorials',
		'rocket_display_tutorials',
		array(
			'description'	=> __( 'This video helps to understand easily the advanced option of "Preloading DNS queries" and the use of the filter <code>rocket_dns_prefetch</code>.', 'rocket' ),
			'url'			=> 'http://www.youtube.com/embed/9jDcg2f-9yM',
			'name'			=> 'tuto_preload_dns_queries',
		)
	);
	add_settings_section( 'rocket_display_faq', __( 'FAQ', 'rocket' ), '__return_false', 'faq' );
	add_settings_field(
		'faq',
		__( 'FAQ', 'rocket' ),
		'rocket_include',
		'faq',
		'rocket_display_faq',
		array(
			'file'	=> 'faq',
		)
	);
	add_settings_section( 'rocket_display_support', __( 'Support', 'rocket' ), '__return_false', 'support' );
	add_settings_field(
		'support',
		__( 'Support', 'rocket' ),
		'rocket_button',
		'support',
		'rocket_display_support',
		array(
				'button'=>array(
					'button_label'	=> __( 'Visit the Support', 'rocket' ),
					'url'			=> 'http://support.wp-rocket.me/',
					'style'			=> 'link',
					),
				'helper_help'=>array(
					'name'			=> 'support',
					'description'	=> __( 'If none of the FAQ answers resolves your problem, you can send your issue to our free support. We will reply as soon as possible.', 'rocket')
				),
		)
	);
?>
	<div class="wrap">

	<h2><?php echo WP_ROCKET_PLUGIN_NAME; ?> <small><sup><?php echo WP_ROCKET_VERSION; ?></sup></small></h2>
	<form action="options.php" method="post" enctype="multipart/form-data">
		<?php settings_fields( 'wp_rocket' ); ?>
		<?php rocket_hidden_fields( array( 'consumer_key', 'consumer_email', 'secret_key', 'license', 'secret_cache_key', 'minify_css_key', 'minify_js_key', 'version' ) ); ?>
		<?php submit_button(); ?>
		<h2 class="nav-tab-wrapper hide-if-no-js">
			<?php if( rocket_valid_key() ) { ?>
				<a href="#tab_basic" class="nav-tab"><?php _e( 'Basic options', 'rocket' ); ?></a>
				<a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced options', 'rocket' ); ?></a>
				<a href="#tab_cdn" class="nav-tab"><?php _e( 'CDN', 'rocket' ); ?></a>
				<?php if( defined( 'WP_RWL' ) ) { ?>
					<a href="#tab_whitelabel" class="nav-tab"><?php _e( 'White Label', 'rocket' ); ?></a>
				<?php } ?>
				<a href="#tab_tools" class="nav-tab"><?php _e( 'Tools', 'rocket' ); ?></a>
				<?php if ( ! rocket_is_white_label() ) { ?>
					<a href="#tab_tutorials" class="nav-tab"><?php _e( 'Tutorials', 'rocket' ); ?></a>
					<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
					<a href="#tab_support" class="nav-tab file-error"><?php _e( 'Support', 'rocket' ); ?></a>
				<?php } ?>
			<?php }else{ ?>
				<a href="#tab_apikey" class="nav-tab"><?php _e( 'License', 'rocket' ); ?></a>
				<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
				<a href="#tab_support" class="nav-tab file-error"><?php _e( 'Support', 'rocket' ); ?></a>
			<?php }  ?>
			<?php
			do_action( 'rocket_tab', rocket_valid_key() );
			?>
		</h2>
		<div id="rockettabs">
			<?php if( rocket_valid_key() ) { ?>
				<div class="rkt-tab" id="tab_basic"><?php do_settings_sections( 'basic' ); ?></div>
				<div class="rkt-tab" id="tab_advanced"><?php do_settings_sections( 'advanced' ); ?></div>
				<div class="rkt-tab" id="tab_cdn"><?php do_settings_sections( 'cdn' ); ?></div>
				<?php $class_hidden = !defined( 'WP_RWL' ) ? ' hidden' : ''; ?>
				<div class="rkt-tab<?php echo $class_hidden; ?>" id="tab_whitelabel"><?php do_settings_sections( 'white_label' ); ?></div>
				<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'tools' ); ?></div>
				<?php if ( ! rocket_is_white_label() ) { ?>
					<div class="rkt-tab" id="tab_tutorials"><?php do_settings_sections( 'tutorials' ); ?></div>
					<div class="rkt-tab rkt-tab-txt" id="tab_faq"><?php do_settings_sections( 'faq' ); ?></div>
					<div class="rkt-tab rkt-tab-txt" id="tab_support"><?php do_settings_sections( 'support' ); ?></div>
				<?php } ?>
			<?php }else{ ?>
				<div class="rkt-tab" id="tab_apikey"><?php do_settings_sections( 'apikey' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq"><?php do_settings_sections( 'faq' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_support"><?php do_settings_sections( 'support' ); ?></div>
			<?php } ?>
			<?php
			do_action( 'rocket_tab_content', rocket_valid_key() );
			?>
		</div>
		<?php submit_button(); ?>
	</form>
<?php
}

/**
 * Tell to WordPress to be confident with our setting, we are clean!
 *
 * @since 1.0
 */
add_action( 'admin_init', 'rocket_register_setting' );
function rocket_register_setting()
{
	register_setting( 'wp_rocket', WP_ROCKET_SLUG, 'rocket_settings_callback' );
}

/**
 * Used with array_filter to remove files without .css extension
 *
 * @since 1.0
 */
function rocket_sanitize_css( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext=='css' ? $file : false;
}

/**
 * Used with array_filter to remove files without .js extension
 *
 * @since 1.0
 */
function rocket_sanitize_js( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext == 'js' ? $file : false;
}

/**
 * Used to clean and sanitize the settings fields
 *
 * @since 1.0
 */
function rocket_settings_callback( $inputs )
{
	if ( isset( $_GET['action'] ) && 'purge_cache' == $_GET['action'] ) {
		return $inputs;
	}

	/*
	 * Option : Minification CSS & JS
	 */
	$inputs['minify_css'] = ! empty( $inputs['minify_css'] ) ? 1 : 0;
	$inputs['minify_js']  = ! empty( $inputs['minify_js'] ) ? 1 : 0;

	/*
	 * Option : Purge delay
	 */
	$inputs['purge_cron_interval'] = isset( $inputs['purge_cron_interval'] ) ? (int)$inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );
	$inputs['purge_cron_unit'] = isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );

	/*
	 * Option : Prefetch DNS requests
	 */
	if ( ! empty( $inputs['dns_prefetch'] ) ) {
		if ( ! is_array( $inputs['dns_prefetch'] ) ) {
			$inputs['dns_prefetch'] = explode( "\n", $inputs['dns_prefetch'] );
		}
		$inputs['dns_prefetch'] = array_map( 'trim', $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = array_map( 'esc_url', $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = (array) array_filter( $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = array_unique( $inputs['dns_prefetch'] );
	} else {
		$inputs['dns_prefetch'] = array();
	}

	/*
	 * Option : Empty the cache of the following pages when updating an article
	 */
	if ( ! empty( $inputs['cache_purge_pages'] ) ) {
		if ( ! is_array( $inputs['cache_purge_pages'] ) ) {
			$inputs['cache_purge_pages'] = explode( "\n", $inputs['cache_purge_pages'] );
		}
		$inputs['cache_purge_pages'] = array_map( 'trim', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_map( 'esc_url', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = (array) array_filter( $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_unique( $inputs['cache_purge_pages'] );
	} else {
		$inputs['cache_purge_pages'] = array();
	}

	/*
	 * Option : Never cache the following pages
	 */
	if ( ! empty( $inputs['cache_reject_uri'] ) ) {
		if ( ! is_array( $inputs['cache_reject_uri'] ) ) {
			$inputs['cache_reject_uri'] = explode( "\n", $inputs['cache_reject_uri'] );
		}
		$inputs['cache_reject_uri'] = array_map( 'trim', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_map( 'esc_url', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = (array) array_filter( $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_unique( $inputs['cache_reject_uri'] );
	} else {
		$inputs['cache_reject_uri'] = array();
	}

	/*
	 * Option : Don't cache pages that use the following cookies
	 */
	if ( ! empty( $inputs['cache_reject_cookies'] ) ) {
		if ( ! is_array( $inputs['cache_reject_cookies'] ) ) {
			$inputs['cache_reject_cookies'] = explode( "\n", $inputs['cache_reject_cookies'] );
		}
		$inputs['cache_reject_cookies'] = array_map( 'trim', $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = array_map( 'sanitize_key', $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = (array) array_filter( $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = array_unique( $inputs['cache_reject_cookies'] );
	} else {
		$inputs['cache_reject_cookies'] = array();
	}

	/*
	 * Option : CSS files to exclude of the minification
	 */
	if ( ! empty( $inputs['exclude_css'] ) ) {
		if ( ! is_array( $inputs['exclude_css'] ) ) {
			$inputs['exclude_css'] = explode( "\n", $inputs['exclude_css'] );
		}
		$inputs['exclude_css'] = array_map( 'trim', $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_map( 'rocket_sanitize_css', $inputs['exclude_css'] );
		$inputs['exclude_css'] = (array) array_filter( $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_unique( $inputs['exclude_css'] );
	} else {
		$inputs['exclude_css'] = array();
	}

	/*
	 * Option : JS files to exclude of the minification
	 */
	if ( ! empty( $inputs['exclude_js'] ) ) {
		if ( ! is_array( $inputs['exclude_js'] ) ) {
			$inputs['exclude_js'] = explode( "\n", $inputs['exclude_js'] );
		}
		$inputs['exclude_js'] = array_map( 'trim', $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_map( 'rocket_sanitize_js', $inputs['exclude_js'] );
		$inputs['exclude_js'] = (array) array_filter( $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_unique( $inputs['exclude_js'] );
	} else {
		$inputs['exclude_js'] = array();
	}


	/*
	 * Option : JS files with deferred loading
	 */
	if ( ! empty( $inputs['deferred_js_files'] ) ) {
		$inputs['deferred_js_files'] = array_unique( $inputs['deferred_js_files'] );
		$inputs['deferred_js_files'] = array_map( 'rocket_sanitize_js', $inputs['deferred_js_files'] );
		$inputs['deferred_js_files'] = array_filter( $inputs['deferred_js_files'] );
	} else {
		$inputs['deferred_js_files'] = array();
	}

	if ( ! $inputs['deferred_js_files'] ) {
		$inputs['deferred_js_wait'] = array();
	} else {
		for ( $i=0; $i<=max( array_keys( $inputs['deferred_js_files'] ) ); $i++ ) {
			if ( ! isset( $inputs['deferred_js_files'][$i] ) ) {
				unset( $inputs['deferred_js_wait'][$i] );
			} else {
				$inputs['deferred_js_wait'][$i] = isset( $inputs['deferred_js_wait'][$i] ) ? '1' : '0';
			}
		}

		$inputs['deferred_js_files'] = array_values( $inputs['deferred_js_files'] );
		ksort( $inputs['deferred_js_wait'] );
		$inputs['deferred_js_wait'] = array_values( $inputs['deferred_js_wait'] );
	}

	/*
	 * Option : JS files of the minification to insert in footer
	 */
	if ( ! empty( $inputs['minify_js_in_footer'] ) ) {
		foreach( $inputs['minify_js_in_footer'] as $k=>$url ) {
			if( in_array( $url, $inputs['deferred_js_files'] ) ) {
				unset( $inputs['minify_js_in_footer'][$k] );
			}
		}

		$inputs['minify_js_in_footer'] = array_filter( array_map( 'rocket_sanitize_js', array_unique( $inputs['minify_js_in_footer'] ) ) );
	} else {
		$inputs['minify_js_in_footer'] = array();
	}

	/*
	 * Option : WL
	 */
	$inputs['wl_plugin_name'] = isset( $inputs['wl_plugin_name'] ) ? wp_strip_all_tags( $inputs['wl_plugin_name'] ) : get_rocket_option( 'wl_plugin_name' );
	$inputs['wl_plugin_URI']  = isset( $inputs['wl_plugin_URI'] )  ? esc_url( $inputs['wl_plugin_URI'] )            : get_rocket_option( 'wl_plugin_URI' );
	$inputs['wl_author']      = isset( $inputs['wl_author'] )      ? wp_strip_all_tags( $inputs['wl_author'] )      : get_rocket_option( 'wl_author' );
	$inputs['wl_author_URI']  = isset( $inputs['wl_author_URI'] )  ? esc_url( $inputs['wl_author_URI'] )            : get_rocket_option( 'wl_author_URI' );
	$inputs['wl_description'] = isset( $inputs['wl_description'] ) ? (array)$inputs['wl_description']               : get_rocket_option( 'wl_description' );
	$inputs['wl_plugin_slug'] = sanitize_key( $inputs['wl_plugin_name'] );

	/*
	 * Option : CDN
	 */

	$inputs['cdn_cnames'] = isset( $inputs['cdn_cnames'] ) ? array_unique( array_filter( $inputs['cdn_cnames'] ) ) : array();

	if ( ! $inputs['cdn_cnames'] ) {
		$inputs['cdn_zone'] = array();
	} else {
		for ( $i = 0; $i <= max( array_keys( $inputs['cdn_cnames'] ) ); $i++ ) {
			if ( ! isset( $inputs['cdn_cnames'][ $i ] ) ) {
				unset( $inputs['cdn_zone'][ $i ] );
			} else {
				$inputs['cdn_zone'][ $i ] = isset( $inputs['cdn_zone'][ $i ] ) ? $inputs['cdn_zone'][ $i ] : 'all';
			}
		}

		$inputs['cdn_cnames'] 	= array_values( $inputs['cdn_cnames'] );
		ksort( $inputs['cdn_zone'] );
		$inputs['cdn_zone'] 	= array_values( $inputs['cdn_zone'] );
	}

	if ( isset( $_FILES['import'] )
		&& preg_match( '/wp-rocket-settings-20\d{2}-\d{2}-\d{2}-[a-f0-9]{13}\.txt/', $_FILES['import']['name'] )
		&& 'text/plain' == $_FILES['import']['type'] ) {
		$file_name 			= $_FILES['import']['name'];
		$_POST_action 		= $_POST['action'];
		$_POST['action'] 	= 'wp_handle_sideload';
		$file 				= wp_handle_sideload( $_FILES['import'] );
		$_POST['action'] 	= $_POST_action;
		$gz 				= 'gz'.strrev( 'etalfni' );
		$settings 			= @file_get_contents( $file['file'] );
		$settings 			= $gz//;
		( $settings );
		$settings 			= unserialize( $settings );
		file_put_contents( $file['file'], '' );
		@unlink( $file['file'] );
		if ( is_array( $settings ) ) {
			$settings['consumer_key']		= $inputs['consumer_key'];
			$settings['consumer_email']		= $inputs['consumer_email'];
			$settings['secret_key']			= $inputs['secret_key'];
			$settings['secret_cache_key']	= $inputs['secret_cache_key'];
			$settings['minify_css_key']		= $inputs['minify_css_key'];
			$settings['minify_js_key']		= $inputs['minify_js_key'];
			$settings['version']			= $inputs['version'];
			$inputs = $settings;
			add_settings_error( 'general', 'settings_updated', __( 'Settings imported and saved.', 'rocket' ), 'updated' );
		}
	}

	if ( ! rocket_valid_key() ) {
		$checked = rocket_check_key( 'live' );
	} else {
		$checked = rocket_check_key( 'transient_1' );
	}

	if ( is_array( $checked ) ) {
		$inputs['consumer_key'] = $checked['consumer_key'];
		$inputs['consumer_email'] = $checked['consumer_email'];
		$inputs['secret_key'] = $checked['secret_key'];
	}

	if ( rocket_valid_key() && ! empty( $inputs['secret_key'] ) && ! isset( $inputs['ignore'] ) ) {
		unset( $inputs['ignore'] );
		add_settings_error( 'general', 'settings_updated', rocket_warning_logged_users(), 'updated' );
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
	}

	return $inputs;
}

/**
 * When our settings are saved: purge, flush, preload!
 *
 * @since 1.0
 *
 * When the White Label Plugin name has changed, redirect on the correct page slug name to avoid a "you dont have permission" false negative annoying page
 * When the settins menu is hidden, redirect on the main settings page to avoid the same thing
 * (Only when a form is sent from our options page )
 *
 * @since 2.1
 */

add_action( 'update_option_' . WP_ROCKET_SLUG, 'rocket_after_save_options', 10, 2 );
function rocket_after_save_options( $oldvalue, $value )
{
	// This values do not need to clean the cache domain
	$removed = array( 'purge_cron_interval' => true, 'purge_cron_unit' => true, 'wl_plugin_name' => true, 'wl_plugin_URI' => true, 'wl_author' => true, 'wl_author_URI' => true, 'wl_description' => true, 'wl_plugin_slug' => true );

	// Create 2 arrays to compare
	$oldvalue_diff 	= array_diff_key( $oldvalue, $removed );
	$value_diff 	= array_diff_key( $value, $removed );

	// If it's different, clean the domain
	if ( md5( serialize( $oldvalue_diff ) ) !== md5( serialize( $value_diff ) ) ) {
		// Purge all cache files
		rocket_clean_domain();
	}

	// Purge all minify cache files
	if ( ! empty( $_POST ) && ( $oldvalue['minify_css'] != $value['minify_css'] || $oldvalue['exclude_css'] != $value['exclude_css'] ) ) {
		rocket_clean_minify('css');
	}

	if ( ! empty( $_POST ) && ( $oldvalue['minify_js'] != $value['minify_js'] || $oldvalue['exclude_js']  != $value['exclude_js'] ) ) {
		rocket_clean_minify( 'js' );
	}

	// Update .htaccess file rules
	flush_rocket_htaccess( ! rocket_valid_key() );

	// Update config file
	rocket_generate_config_file();

	// Set WP_CACHE constant in wp-config.php
	if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}

	// Redirect on the correct page slug name to avoid false negative error message
	if ( ! empty( $_POST ) && $oldvalue['wl_plugin_name'] != $value['wl_plugin_name'] &&
		isset( $_POST['option_page'], $_POST['action'] ) && 'wp_rocket' == $_POST['option_page'] && 'update' == $_POST['action'] )
	{
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_redirect( admin_url( 'options-general.php?page=' . sanitize_key( $value['wl_plugin_name'] ) . '&settings-updated=true' ) );
		die();
	}
}

/**
 * When purge settings are saved we change the scheduled purge
 *
 * @since 1.0
 */
add_filter( 'pre_update_option_'.WP_ROCKET_SLUG, 'rocket_pre_main_option', 10, 2 );
function rocket_pre_main_option( $newvalue, $oldvalue )
{
	if ( ( $newvalue['purge_cron_interval'] != $oldvalue['purge_cron_interval'] ) || ( $newvalue['purge_cron_unit'] != $oldvalue['purge_cron_unit'] ) ) {
		// Clear WP Rocket cron
		if ( wp_next_scheduled( 'rocket_purge_time_event' ) ) {
			wp_clear_scheduled_hook( 'rocket_purge_time_event' );
		}
	}

	// Regenerate the minify key if CSS files have been modified
	if ( ( isset( $newvalue['minify_css'], $oldvalue['minify_css'] ) && $newvalue['minify_css'] != $oldvalue['minify_css'] )
		|| ( isset( $newvalue['exclude_css'], $oldvalue['exclude_css'] ) && $newvalue['exclude_css'] != $oldvalue['exclude_css'] )
	) {
		$newvalue['minify_css_key'] = create_rocket_uniqid();
	}

	// Regenerate the minify key if JS files have been modified
	if ( ( isset( $newvalue['minify_js'], $oldvalue['minify_js'] ) && $newvalue['minify_js'] != $oldvalue['minify_js'] )
		|| ( isset( $newvalue['exclude_js'], $oldvalue['exclude_js'] ) && $newvalue['exclude_js'] != $oldvalue['exclude_js'] )
		|| ( isset( $newvalue['minify_js_in_footer'], $oldvalue['minify_js_in_footer'] ) && $newvalue['minify_js_in_footer'] != $oldvalue['minify_js_in_footer'] )
	) {
		$newvalue['minify_js_key'] = create_rocket_uniqid();
	}

	if ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) ) {
		rocket_generate_advanced_cache_file();
	}

	if ( $keys = get_transient( WP_ROCKET_SLUG ) ) {
		delete_transient( WP_ROCKET_SLUG );
		$newvalue = array_merge( $newvalue, $keys );
	}

	return $newvalue;
}

/**
 * Function used to print all hidden fields from rocket to avoid the loss of these.
 *
 * @since 2.1
 */
function rocket_hidden_fields( $fields )
{
	if ( ! is_array( $fields ) ) {
		return;
	}

	foreach ( $fields as $field ) {
		echo '<input type="hidden" name="wp_rocket_settings[' . $field . ']" value="' . esc_attr( get_rocket_option( $field ) ) . '" />';
	}
}

/**
 * Outputs the form used by the importers to accept the data to be imported
 *
 * @since 2.2
 */
function rocket_import_upload_form() {

	/**
	 * Filter the maximum allowed upload size for import files.
	 *
	 * @since (WordPress) 2.3.0
	 *
	 * @see wp_max_upload_size()
	 *
	 * @param int $max_upload_size Allowed upload size. Default 1 MB.
	 */
	$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // Filter from WP Core
	$size = size_format( $bytes );
	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		?><div class="error"><p><?php _e('Before you can upload your import file, you will need to fix the following error:'); ?></p>
		<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
	}else{
		?>
		<p>
		<input type="file" id="upload" name="import" size="25" />
		<br />
		<label for="upload"><?php echo apply_filters( 'rocket_help', __( 'Choose a file from your computer:' ) . ' (' . sprintf( __('Maximum size: %s' ), $size ) . ')', 'upload', 'help' ); ?></label>
		<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
		</p>
		<?php submit_button( __( 'Upload file and import settings', 'rocket' ), 'button', 'import' );
	}
}