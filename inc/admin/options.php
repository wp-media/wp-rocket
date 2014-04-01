<?php
defined( 'ABSPATH' ) or	die( __( 'Cheatin&#8217; uh?', 'rocket' ) );


/**
 * Add submenu in menu "Settings"
 *
 * @since 1.0
 *
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
 *
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
		$class			= isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : sanitize_html_class( $args['name'] ) ;
		$placeholder 	= isset( $args['placeholder'] ) ? 'placeholder="'. $args['placeholder'].'" ' : '';
		$label 			= isset( $args['label'] ) ? $args['label'] : '';
		$readonly 		= $args['name'] == 'consumer_key' && rocket_valid_key() ? ' readonly="readonly"' : '';

		if( ! isset( $args['fieldset'] ) || 'start' == $args['fieldset'] ){
			echo '<fieldset class="fieldname-'.sanitize_html_class( $args['name'] ).' fieldtype-'.sanitize_html_class( $args['type'] ).'">';
		}

		switch( $args['type'] ) {
			case 'number' :
			case 'text' :

				$value = esc_attr( get_rocket_option( $args['name'], '' ) );
				$number_options = $args['type']=='number' ? ' min="0" class="small-text"' : '';

				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input<?php if( $args['name'] == 'consumer_key' ){ echo ' autocomplete="off"'; } ?> type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>

				<?php
				if( $args['name'] == 'consumer_key' ){

					if( !rocket_valid_key() )
					{
						echo '<span style="font-weight:bold;color:red">'. __('Key is not valid', 'rocket') .'</span>';
					}else{
						echo '<span style="font-weight:bold;color:green">'. __('Key is valid', 'rocket') .'</span>';
					}

				}

			break;

			case 'textarea' :

				$t_temp = get_rocket_option( $args['name'], '' );
				$value = !empty( $t_temp ) ? esc_textarea( implode( "\n" , $t_temp ) ) : '';

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
 *
 */

function rocket_defered_module()
{ ?>

		<legend class="screen-reader-text"><span><?php _e( '<b>JS</b> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>

		<div id="rkt-drop-deferred">

			<?php

			$deferred_js_files = get_rocket_option( 'deferred_js_files' );
			$deferred_js_wait = get_rocket_option( 'deferred_js_wait' );

			if( $deferred_js_files )
			{

				foreach( $deferred_js_files as $k=>$_url )
				{
					$checked = isset( $deferred_js_wait[$k] ) ? checked( $deferred_js_wait[$k], '1', false ) : '';
					// The loop on files
				?>

				<div class="rkt-drag-deferred">

					<img class="rkt-move-deferred hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
					</label>
					<span class="rkt-delete-deferred hide-if-no-js rkt-cross"><?php _e( 'Delete' ); ?></span>

				</div>
				<!-- .rkt-drag-deferred -->

				<?php }
			}
			else
			{
				// If no files yet, use this template inside #rkt-drop-deferred
				?>

				<div class="rkt-drag-deferred">

					<img class="rkt-move-deferred hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Wait until this file is loaded ?', 'rocket' ); ?>
					</label>

				</div>
				<!-- .rkt-drag-deferred -->

			<?php } ?>

		</div>
		<!-- .rkt-drop-deferred -->

		<?php // Clone Template ?>

		<div class="rkt-model-deferred rkt-drag-deferred hide-if-js">

			<img class="rkt-move-deferred hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />

			<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />

			<label>
				<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][]" value="1" /> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
			</label>
			<span class="rkt-delete-deferred hide-if-no-js rkt-cross"><?php _e( 'Delete' ); ?></span>

		</div>
		<!-- .rkt-model-deferred-->

		<p><a href="javascript:void(0)" id="rkt-clone-deferred" class="hide-if-no-js button-secondary"><?php _e( 'Add an URL', 'rocket' ); ?></a></p>

<?php
}



/**
 * Used to display the CNAMES module on settings form
 *
 * @since 2.1
 *
 */

function rocket_cnames_module()
{ ?>

	<fieldset>
		<legend class="screen-reader-text"><span><?php _e( 'Replace site\'s hostname with:', 'rocket' ); ?></span></legend>

		<div id="rkt-cnames">

			<?php

			$cnames = get_rocket_option( 'cdn_cnames' );
			$cnames_zone = get_rocket_option( 'cdn_zone' );

			if( $cnames )
			{

				foreach( $cnames as $k=>$_url )
				{ ?>

					<div class="rkt-cname">

						<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][<?php echo $k; ?>]" value="<?php echo esc_attr( $_url ); ?>" />

						<label>
							<?php _e( 'reserved for', 'rocket' ); ?>
							<select name="wp_rocket_settings[cdn_zone][<?php echo $k; ?>]">
								<option value="all" <?php selected( $cnames_zone[$k], 'all' ); ?>><?php _e( 'All files', 'rocket' ); ?></option>
								<option value="images" <?php selected( $cnames_zone[$k], 'images' ); ?>><?php _e( 'Images', 'rocket' ); ?></option>
								<option value="css_and_js" <?php selected( $cnames_zone[$k], 'css_and_js' ); ?>>CSS & JavaScript</option>
							</select>
						</label>
						<span class="rkt-delete-cname hide-if-no-js rkt-cross"><?php _e( 'Delete' ); ?></span>

					</div>

				<?php
				}

			}
			else
			{

				// If no files yet, use this template inside #rkt-cnames
				?>

				<div class="rkt-cname">

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][]">
							<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
							<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
							<option value="css_and_js">CSS & JavaScript</option>
						</select>
					</label>

				</div>

			<?php } ?>

		</div>

		<?php // Clone Template ?>
		<div class="rkt-model-cname rkt-cname hide-if-js">

			<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

			<label>
				<?php _e( 'reserved for', 'rocket' ); ?>
				<select name="wp_rocket_settings[cdn_zone][]">
					<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
					<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
					<option value="css_and_js">CSS & JavaScript</option>
				</select>
			</label>
			<span class="rkt-delete-cname hide-if-no-js rkt-cross"><?php _e( 'Delete' ); ?></span>

		</div>

		<p><a href="javascript:void(0)" id="rkt-clone-cname" class="hide-if-no-js button-secondary"><?php _e( 'Add CNAME', 'rocket' ); ?></a></p>

	</fieldset>

<?php
}


/**
 * Used to display buttons on settings form, tools tab
 *
 * @since 1.1.0
 *
 */

function rocket_button( $args )
{
	$button = $args['button'];
	$desc = isset( $args['helper_description'] ) ? $args['helper_description'] : null;
	$help = isset( $args['helper_help'] ) ? $args['helper_help'] : null;
	$warning = isset( $args['helper_warning'] ) ? $args['helper_warning'] : null;
	$class = sanitize_html_class( strip_tags( $button['button_label'] ) );


	if( !empty( $help ) )
	{
		$help = '<p class="description help '.$class.'">'.$help['description'].'</p>';
	}
	if( !empty( $desc ) )
	{
		$desc = '<p class="description desc '.$class.'">'.$desc['description'].'</p>';
	}
	if( !empty( $warning ) )
	{
		$warning = '<p class="description warning file-error '.$class.'"><b>'.__( 'Warning: ', 'rocket' ) . '</b>' . $warning['description'].'</p>';
	}
?>
	<fieldset class="fieldname-<?php echo $class; ?> fieldtype-button">
		<a href="<?php echo esc_url( $button['url'] ); ?>" class="button-secondary"><?php echo esc_html( strip_tags( $button['button_label'] ) ); ?></a>

		<?php echo apply_filters( 'rocket_help', $desc, sanitize_key( strip_tags( $button['button_label'] ) ), 'description' ); ?>
		<?php echo apply_filters( 'rocket_help', $help, sanitize_key( strip_tags( $button['button_label'] ) ), 'help' ); ?>
		<?php echo apply_filters( 'rocket_help', $warning, sanitize_key( strip_tags( $button['button_label'] ) ), 'warning' ); ?>

	</fieldset>
<?php
}



/**
 * The main settings page construtor using the required functions from WP
 * @since 1.0
 *
 * Add tabs, tools tab and change options severity
 * @since 1.1.0
 *
 */

function rocket_display_options()
{
	// Clé API
	add_settings_section( 'rocket_display_apikey_options', __( 'API KEY', 'rocket' ), '__return_false', 'apikey' );
	add_settings_field(
		'rocket_api_key',
		__( 'API Key:<br/><span class="description">(Licence validation)</span>', 'rocket' ),
		'rocket_field',
		'apikey',
		'rocket_display_apikey_options',
		array(
			array(
				'type'         => 'text',
				'label_for'    => 'consumer_key',
				'label_screen' => __( 'API Key', 'rocket' ),
			),
			array(
				'type'         => 'helper_help',
				'name'         => 'api_key',
				'description'  => __( 'Thank you to enter the API key obtained after your purchase.', 'rocket' )
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
									  __( 'This reduces the number of HTTP requests and improves the loading time.', 'rocket' )
			),
			array(
				'type'			=> 'helper_warning',
				'name'			=> 'minify_help1',
				'description'  => __( 'Concatenating files can cause display errors.', 'rocket' ),
			),
			array(
				'display'		=> !rocket_is_white_label(),
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
				'description'  => __( 'Enter the URL of additionnal pages to purge when updating a post (one per line).','rocket' ) . '<br/>' .
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
	        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=resetwl' ), 'resetwl' ),
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
?>
	<div class="wrap">
	<?php if( version_compare( $GLOBALS['wp_version'], '3.8' )<0 || !rocket_is_white_label() ) { ?>
	<div id="icon-rocket" class="icon32"></div>
	<?php } ?>
	<h2><?php echo WP_ROCKET_PLUGIN_NAME; ?> <small><sup><?php echo WP_ROCKET_VERSION; ?></sup></small></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'wp_rocket' ); ?>
		<?php rocket_hidden_fields( array( 'consumer_key', 'secret_key', 'secret_cache_key', 'minify_css_key', 'minify_js_key', 'version' ) ); ?>
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
				<?php if( !rocket_is_white_label() ) { ?>
					<a href="#tab_tutos" class="nav-tab"><?php _e( 'Tutorials', 'rocket' ); ?></a>
					<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
					<a href="#tab_support" class="nav-tab file-error"><?php _e( 'Support', 'rocket' ); ?></a>
				<?php } ?>
			<?php }else{ ?>
				<a href="#tab_apikey" class="nav-tab"><?php _e( 'API KEY', 'rocket' ); ?></a>
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
				<?php if( !rocket_is_white_label() ) { ?>
				<div class="rkt-tab rkt-tab-txt" id="tab_tutos">
					<?php include( WP_ROCKET_ADMIN_PATH . 'tutorials.php' ); ?>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq">
					<?php include( WP_ROCKET_ADMIN_PATH . 'faq.php' ); ?>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_support">
					<p><?php _e( 'If none of the FAQ answers resolves your problem, you can tell us your issue on our <a href="http://support.wp-rocket.me/" target="_blank">Support</a>. We will reply as soon as possible.', 'rocket');?></p>
					<p><a href="http://support.wp-rocket.me/" class="button-primary" target="_blank"><?php _e( 'Go to Support', 'rocket' );?></a></p>
				</div>
			<?php } ?>
			<?php }else{ ?>
				<div class="rkt-tab" id="tab_apikey"><?php do_settings_sections( 'apikey' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq">
					<?php include( WP_ROCKET_ADMIN_PATH . 'faq.php' ); ?>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_support">
					<p><?php _e( 'If none of the FAQ answers resolves your problem, you can tell us your issue on our <a href="http://support.wp-rocket.me/" target="_blank">Support</a>. We will reply as soon as possible.', 'rocket');?></p>
					<p><a href="http://support.wp-rocket.me/" class="button-primary" target="_blank"><?php _e( 'Go to Support', 'rocket' );?></a></p>
				</div>
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
 *
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
 *
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
 *
 */

function rocket_sanitize_js( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext  = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext == 'js' ? $file : false;
}



/**
 * Get relative url
 * Clean URL file to get only the equivalent of REQUEST_URI
 * ex: rocket_clean_exclude_file( 'http://www.geekpress.fr/referencement-wordpress/') return /referencement-wordpress/
 *
 * @since 1.0
 * @since 1.3.5 Redo the function
 *
 */

function rocket_clean_exclude_file( $file )
{
	if( !$file ) {
		return false;
	}

	$path = parse_url( $file, PHP_URL_PATH );
    return $path;
}



/**
 * Used to clean and sanitize the settings fields
 *
 * @since 1.0
 *
 */

function rocket_settings_callback( $inputs )
{
	if( isset( $_GET['action'] ) && $_GET['action'] == 'purge_cache' )
	{
		return $inputs;
	}

	/*
	 * Option : Minification CSS & JS
	 */
	
	$inputs['minify_css'] = isset( $inputs['minify_css'] );
	$inputs['minify_js'] = isset( $inputs['minify_js'] );
	
	/*
	 * Option : Purge delay
	 */

	$inputs['purge_cron_interval'] = isset( $inputs['purge_cron_interval'] ) ? (int)$inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );

	$inputs['purge_cron_unit'] = isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );


	/*
	 * Option : Prefetch DNS requests
	 */

	if ( ! empty( $inputs['dns_prefetch'] ) )
	{
		$inputs['dns_prefetch'] = array_unique( (array) array_filter( array_map( 'esc_url', array_map( 'trim', explode( "\n", $inputs['dns_prefetch'] ) ) ) ) );
	}else{
		$inputs['dns_prefetch'] = array();
	}


	/*
	 * Option : Empty the cache of the following pages when updating an article
	 */

	if ( ! empty( $inputs['cache_purge_pages'] ) )
	{
		$inputs['cache_purge_pages'] = array_unique( (array) array_filter( array_map( 'rocket_clean_exclude_file', array_map( 'esc_url', array_map( 'trim', explode( "\n", $inputs['cache_purge_pages'] ) ) ) ) ) );
	}else{
		$inputs['cache_purge_pages'] = array();
	}


	/*
	 * Option : Never cache the following pages
	 */

	if ( ! empty( $inputs['cache_reject_uri'] ) )
	{
		$inputs['cache_reject_uri'] = array_unique( (array) array_filter( array_map( 'rocket_clean_exclude_file', array_map( 'esc_url', array_map( 'trim', explode( "\n", $inputs['cache_reject_uri'] ) ) ) ) ) );
	}else{
		$inputs['cache_reject_uri'] = array();
	}


	/*
	 * Option : Don't cache pages that use the following cookies
	 */

	if ( ! empty( $inputs['cache_reject_cookies'] ) )
	{
		$inputs['cache_reject_cookies'] = array_unique( (array) array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( "\n", $inputs['cache_reject_cookies'] ) ) ) ) );
	}else{
		$inputs['cache_reject_cookies'] = array();
	}


	/*
	 * Option : CSS files to exclude of the minification
	 */

	if ( ! empty( $inputs['exclude_css'] ) )
	{
		$inputs['exclude_css'] = array_unique( (array) array_filter( array_map( 'rocket_sanitize_css', array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_css'] ) ) ) ) ) );
	}else{
		$inputs['exclude_css'] = array();
	}


	/*
	 * Option : JS files to exclude of the minification
	 */

	if ( ! empty( $inputs['exclude_js'] ) )
	{
		$inputs['exclude_js'] = array_unique( (array) array_filter( array_map( 'rocket_sanitize_js', array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_js']) ) ) ) ) );
	}else{
		$inputs['exclude_js'] = array();
	}


	/*
	 * Option : JS files with deferred loading
	 */

	if ( ! empty( $inputs['deferred_js_files'] ) )
	{
		$inputs['deferred_js_files'] = array_filter( array_map( 'rocket_sanitize_js', array_unique( $inputs['deferred_js_files'] ) ) );
	}
	else
	{
		$inputs['deferred_js_files'] = array();
	}


	if ( ! $inputs['deferred_js_files'] )
	{
		$inputs['deferred_js_wait'] = array();
	}
	else
	{

		for ( $i=0; $i<=max( array_keys( $inputs['deferred_js_files'] ) ); $i++ )
		{

			if( !isset( $inputs['deferred_js_files'][$i] ) )
			{
				unset( $inputs['deferred_js_wait'][$i] );
			}
			else
			{
				$inputs['deferred_js_wait'][$i] = isset( $inputs['deferred_js_wait'][$i] ) ? '1' : '0';
			}

		}

		$inputs['deferred_js_files'] = array_values( $inputs['deferred_js_files'] );
		ksort( $inputs['deferred_js_wait'] );
		$inputs['deferred_js_wait'] = array_values( $inputs['deferred_js_wait'] );

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


	if( ! $inputs['cdn_cnames'] )
	{
		$inputs['cdn_zone'] = array();
	}
	else
	{

		for( $i=0; $i<=max(array_keys($inputs['cdn_cnames'])); $i++ )
		{

			if( !isset( $inputs['cdn_cnames'][$i] ) )
			{
				unset( $inputs['cdn_zone'][$i] );
			}
			else
			{
				$inputs['cdn_zone'][$i] = isset( $inputs['cdn_zone'][$i] ) ? $inputs['cdn_zone'][$i] : 'all';
			}

		}

		$inputs['cdn_cnames'] = array_values( $inputs['cdn_cnames'] );
		ksort( $inputs['cdn_zone'] );
		$inputs['cdn_zone'] = array_values( $inputs['cdn_zone'] );

	}


	/*
	 * Option : Consumer Key
	 */

	if ( $inputs['consumer_key'] == hash( 'crc32', rocket_get_domain( home_url() ) ) ) {

		$response = wp_remote_get( WP_ROCKET_WEB_VALID, array( 'timeout'=>30 ) );
		if ( ! is_a( $response, 'WP_Error' ) && 32 == strlen( $response['body'] ) ) {
			$inputs['secret_key'] = $response['body'];
		}

	}

	rocket_renew_box( 'rocket_warning_logged_users' );

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
	
	// Check if a plugin translation is activated
	if ( rocket_has_translation_plugin_active() ) {
		// Purge all cache files
		rocket_clean_domain_for_all_langs();
	} else {
		// Purge all cache files
		rocket_clean_domain();	
	}
	
	// Purge all minify cache files
	if( !empty( $_POST ) && ( $oldvalue['minify_css'] != $value['minify_css'] || $oldvalue['exclude_css'] != $value['exclude_css'] ) ) {
		rocket_clean_minify('css');	
	}
	
	if( !empty( $_POST ) && ( $oldvalue['minify_js'] != $value['minify_js'] || $oldvalue['exclude_js']  != $value['exclude_js'] ) ) {
		rocket_clean_minify( 'js' );	
	}

	// Update .htaccess file rules
	if( !empty( $_POST ) && $oldvalue['wl_plugin_name'] != $value['wl_plugin_name'] &&
		isset( $_POST['option_page'], $_POST['action'] ) && 'wp_rocket'==$_POST['option_page'] && 'update'==$_POST['action'] )
	{
		flush_rocket_htaccess( true, $oldvalue['wl_plugin_name'] );
	}
	else
	{
		flush_rocket_htaccess( !rocket_valid_key(), $value['wl_plugin_name'] );
	}

	// Update config file
	rocket_generate_config_file();

	// Set WP_CACHE constant in wp-config.php
	set_rocket_wp_cache_define( true );

	// Redirect on the correct page slug name to avoid false negative error message
	if( !empty( $_POST ) && $oldvalue['wl_plugin_name'] != $value['wl_plugin_name'] &&
		isset( $_POST['option_page'], $_POST['action'] ) && 'wp_rocket'==$_POST['option_page'] && 'update'==$_POST['action'] )
	{
		add_settings_error('general', 'settings_updated', __('Settings saved.'), 'updated');
		set_transient('settings_errors', get_settings_errors(), 30);
		wp_redirect( admin_url( 'options-general.php?page=' . sanitize_key( $value['wl_plugin_name'] ) . '&settings-updated=true' ) );
		die();
	}
}



/**
 * When purge settings are saved we change the scheduled purge
 *
 * @since 1.0
 *
 */

add_filter( 'pre_update_option_'.WP_ROCKET_SLUG, 'rocket_pre_main_option', 10, 2 );
function rocket_pre_main_option( $newvalue, $oldvalue )
{

	if( ( $newvalue['purge_cron_interval'] != $oldvalue['purge_cron_interval'] ) || ( $newvalue['purge_cron_unit']!=$oldvalue['purge_cron_unit'] ) )
	{

		// Clear WP Rocket cron
		if ( wp_next_scheduled( 'rocket_purge_time_event' ) )
		{
			wp_clear_scheduled_hook( 'rocket_purge_time_event' );
		}

	}

	// Regenerate the minify key if CSS files have been modified
	if( ( isset( $newvalue['minify_css'], $oldvalue['minify_css'] ) && $newvalue['minify_css'] != $oldvalue['minify_css'] )
		|| ( isset( $newvalue['exclude_css'], $oldvalue['exclude_css'] ) && $newvalue['exclude_css'] != $oldvalue['exclude_css'] )
	) {
		$newvalue['minify_css_key'] = create_rocket_uniqid();
	}
	
	// Regenerate the minify key if JS files have been modified
	if( ( isset( $newvalue['minify_js'], $oldvalue['minify_js'] ) && $newvalue['minify_js'] != $oldvalue['minify_js'] )
		|| ( isset( $newvalue['exclude_js'], $oldvalue['exclude_js'] ) && $newvalue['exclude_js'] != $oldvalue['exclude_js'] )
	) {
		$newvalue['minify_js_key'] = create_rocket_uniqid();
	}

	return $newvalue;

}

/**
 * Function used to print all hidden fields from rocket to avoid the loss of these.
 *
 * @since 2.1
 *
 */

function rocket_hidden_fields( $fields )
{
	if( !is_array( $fields ) ) {
		return;
	}
	foreach( $fields as $field ) {
		echo '<input type="hidden" name="wp_rocket_settings['.$field.']" value="' . esc_attr( get_rocket_option( $field ) ) . '" />';
	}
}