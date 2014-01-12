<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * Add submenu in menu "Settings"
 *
 * @since 1.0
 *
 */

add_action( 'admin_menu', 'rocket_admin_menu' );
function rocket_admin_menu()
{
	add_options_page( 'WP Rocket', 'WP Rocket', 'manage_options', 'wprocket', 'rocket_display_options' );
}



/**
 * Used to display fields on settings form
 *
 * @since 1.0
 *
 */

function rocket_field( $args )
{
	if( !is_array( reset( $args ) ) )
		$args = array( $args );

	$full = $args;

	foreach ( $full as $args )
	{
		$args['name'] 	= isset( $args['name'] ) ? $args['name'] : $args['label_for'];
		$description 	= isset( $args['description'] ) ? '<p class="description">'.$args['description'].'</p>' : '';
		$placeholder 	= isset( $args['placeholder'] ) ? 'placeholder="'. $args['placeholder'].'" ' : '';
		$label 			= isset( $args['label'] ) ? $args['label'] : '';
		$readonly 		= $args['name'] == 'consumer_key' && rocket_valid_key() ? ' readonly="readonly"' : '';

		if( !isset( $args['fieldset'] ) || $args['fieldset']=='start' )
			echo '<fieldset>';

		switch( $args['type'] )
		{
			case 'number' :
			case 'text' :

				$value = esc_attr( get_rocket_option( $args['name'], '' ) );
				$number_options = $args['type']=='number' ? ' min="0" class="small-text"' : '';

				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>

				<?php
				if( $args['name'] == 'consumer_key' )

					if( !rocket_valid_key() )
						echo '<span style="font-weight:bold;color:red">'. __('Key is not valid', 'rocket') .'</span>';
					else
						echo '<span style="font-weight:bold;color:green">'. __('Key is valid', 'rocket') .'</span>';


				echo $description;

			break;

			case 'textarea' :

				$t_temp = get_rocket_option( $args['name'], '' );
				$value = !empty( $t_temp ) ? esc_textarea( implode( "\n" , $t_temp ) ) : '';

				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="50" rows="5"><?php echo $value; ?></textarea>
					</label>
					<?php echo $description; ?>

				<?php
			break;

			case 'checkbox' : ?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="checkbox" id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1" <?php checked( get_rocket_option( $args['name'], 0 ), 1 ); ?>/> <?php echo $args['label']; ?>
					</label>
					<?php echo $description; ?>

			<?php
			break;

			case 'select' : ?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label>	<select id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]">
							<?php foreach( $args['options'] as $val => $title) : ?>
								<option value="<?php echo $val; ?>" <?php selected( get_rocket_option( $args['name'] ), $val ); ?>><?php echo $title; ?></option>
							<?php endforeach; ?>
							</select>
					<?php echo $label; ?>
					</label>
					<?php echo $description; ?>

			<?php
			break;

			default : _e( 'Missing TYPE ! ', 'rocket' );
		}

		if( !isset( $args['fieldset'] ) || $args['fieldset']=='end' )
			echo '</fieldset>';
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
	
	<fieldset>
		<legend class="screen-reader-text"><span><?php _e( '<strong>JS</strong> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>
		
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
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Wait until this file is loaded ?', 'rocket' ); ?>
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
		<p class="description"><?php _e( 'You can add JavaScript files that will be loaded asynchronously at the same time as the page loads.', 'rocket' ); ?></p>
		<p class="hide-if-js"><?php _e( 'Empty the field to remove it.', 'rocket' ); ?></p>
		<p class="description"><?php _e( '<strong>Warning :</strong> you must specify the complete URL of the files.', 'rocket' ); ?></p>
		
	</fieldset>

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
?>
	<fieldset>
		<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary"/><?php echo esc_html( strip_tags( $args['button_label'] ) ); ?></a>
		<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
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
		__( 'API Key :<br /><span class="description">(WP Rocket validation)</span>', 'rocket' ),
		'rocket_field',
		'apikey',
		'rocket_display_apikey_options',
		array(
			'type'         => 'text',
			'label_for'    => 'consumer_key',
			'label_screen' => __('API Key', 'rocket'),
			'description'  => __('Thank you to enter the API key obtained when buying.', 'rocket' )
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
			'type'         => 'checkbox',
			'label'        => __('Enable Lazy loading images.', 'rocket' ),
			'label_for'    => 'lazyload',
			'label_screen' => __( 'Lazyload:', 'rocket' ),
			'description'  => __( 'LazyLoad displays images on a page only when they are visible to the user. <br/> This reduces the number of HTTP requests mechanism and improves the loading time.', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_minify',
		 __( 'Files optimisation: <br/> <span class="description">(Minification & Concatenation)</span>', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => 'HTML',
				'name'         => 'minify_html',
				'label_screen' => __( 'Files minification', 'rocket' )
			),
			array(
				'type'         => 'checkbox',
				'label'        => 'CSS',
				'name'         => 'minify_css',
				'label_screen' => __( 'Files minification', 'rocket' )
			),
			array(
				'type'		   => 'checkbox',
				'label'		   => 'JS',
				'name'		   => 'minify_js',
				'label_screen' => __( 'Files minification', 'rocket' ),
				'description'  => sprintf( __( 'Minification removes any space and comments present in the CSS and Javascript files.<br/>This mechanism reduces the weight of each file and allows a faster reading of browsers and search engines.<br/>Concatenation combines all CSS and Javascript files.<br/>This reduces the number of HTTP requests and improves the loading time.<br/><strong style="color:#FF0000;">Warning: concatenating files can cause display errors. In case of any errors we recommend that you disable this option or watch the following videos: <a href="%s" class="fancybox">%s</a></strong>.' ,'rocket' ), 'http://www.youtube.com/embed/ziXSvZgxLk', 'http://www.youtube.com/embed/ziXSvZgxLk' ) )
		)
	);
	add_settings_field(
		'rocket_mobile',
		__( 'Mobile cache:', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			'type'		   => 'checkbox',
			'label'		   => __( 'Enable caching for mobile devices.', 'rocket' ),
			'label_for'	   => 'cache_mobile',
			'label_screen' => __( 'Mobile cache:', 'rocket' ),
			'description'  => __( '<strong style="color:#FF0000;">Warning: if you use the plugin <a target="_blank" href="http://wordpress.org/plugins/wptouch/">WP Touch</a>, <a href="http://wordpress.org/plugins/wp-mobile-detector/" target="_blank">WP Mobile Detector</a>, <a href="http://wordpress.org/plugins/wiziapp-create-your-own-native-iphone-app" target="_blank">wiziApp</a> or <a href="http://wordpress.org/plugins/wordpress-mobile-pack/" target="_blank">WordPress Mobile Pack</a>, you should not enable this option.</strong>', 'rocket' )
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
			'description'  => ''
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
			'description'  => ''
		)
	);
	add_settings_field(
		'rocket_purge',
		__( 'Purge delay', 'rocket' ),
		'rocket_field',
		'basic',
		'rocket_display_main_options',
		array(
			array(
				'type'         => 'number',
				'label_for'    => 'purge_cron_interval',
				'label_screen' => __( 'Purge delay', 'rocket' ),
				'fieldset'     => 'start'
			),
			array(
				'type'		   => 'select',
				'label_for'	   => 'purge_cron_unit',
				'label_screen' => __( 'Unit of time', 'rocket' ),
				'fieldset'	   => 'end',
				'description'  => __( 'By default purge time is 12 hours, this means that once created, the cache files are automatically removed after 12 hours before being recreated. <br/> This can be useful if you display your latest tweets or rss feeds in your sidebar, for example. <br/> Specify 0 for unlimited life.', 'rocket' ),
				'options' => array(
								'SECOND_IN_SECONDS' => __( 'second(s)', 'rocket' ),
								'MINUTE_IN_SECONDS' => 'minute(s)',
								'HOUR_IN_SECONDS'   => __( 'hour(s)', 'rocket' ),
								'DAY_IN_SECONDS'    => __( 'day(s)', 'rocket' )
							)
				)
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
			'type'         => 'textarea',
			'label_for'    => 'dns_prefetch',
			'label_screen' => __('Prefetch DNS requests', 'rocket' ),
			'description'  => sprintf( __( 'DNS prefetching is a way for browsers to anticipate the DNS resolution of external domains from your site.<br/>This mechanism reduces the latency of some external files.<br/>To Learn more about this option and how to use it correctly, we advise you to watch the following video: <a href="%s" class="fancybox">%s</a><br/><strong>Warning:</strong> Enter the domain names without their protocol, for example: <code>//ajax.googleapis.com</code> without <code>http:</code> (one per line).', 'rocket' ), 'http://www.youtube.com/embed/ElJCtUidLwc', 'http://www.youtube.com/embed/ElJCtUidLwc' )
		)
	);
	add_settings_field(
		'rocket_purge_pages',
		__( 'Empty the cache of the following pages when updating an article:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_purge_pages',
			'label_screen' => __( 'Empty the cache of the following pages when updating a post:', 'rocket' ),
			'description'  => __('Enter the URL of additionnal page to purge when updating a post (one per line). <br/>It’s possible to use regular expressions (regex). <br/><strong>NB</strong>: When you update a post or when a comment is posted, the home page, categories, and tags associated whith the post are automatically removed from the cache, and the recreated by the WP Rocket Bot', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_reject_uri',
		__( 'Never cache the following pages :', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_uri',
			'label_screen' => __( 'Never cache the following pages :', 'rocket' ),
			'description'  => __( 'Enter the URL of pages to reject (one per line). <br/> You can use regular expressions (regex).', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_reject_cookies',
		__( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			'type'         => 'textarea',
			'label_for'    => 'cache_reject_cookies',
			'label_screen' => __( 'Don\'t cache pages that use the following cookies:', 'rocket' ),
			'description'  => __( 'List the names of the cookies (one per line).', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_minify_pretty_url',
		 __( 'Use "pretty URL" in minification for:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			array(
				'type'         => 'checkbox',
				'label'        => 'CSS',
				'name'         => 'minify_pretty_url_css',
				'label_screen' => __( 'Files minification', 'rocket' )
			),
			array(
				'type'		   => 'checkbox',
				'label'		   => 'JS',
				'name'		   => 'minify_pretty_url_js',
				'label_screen' => __( 'Files minification', 'rocket' ),
				'description'  => '' )
		)
	);
	add_settings_field(
		'rocket_exclude_css',
		__( '<strong>CSS</strong> files to exclude of the minification:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_css',
			'label_screen' => __( '<strong>CSS</strong> files to exclude of the minification:', 'rocket' ),
			'description'  => __( 'Specify the URL of <strong>CSS </strong> files to reject (one per line).', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_exclude_js',
		__( '<strong>JS</strong> files to exclude of the minification:', 'rocket' ),
		'rocket_field',
		'advanced',
		'rocket_display_imp_options',
		array(
			'type'         => 'textarea',
			'label_for'    => 'exclude_js',
			'label_screen' => __( '<strong>JS</strong> files to exclude of the minification:', 'rocket' ),
			'description'  => __('Specify the URL of <strong> JS </strong> files to reject (one per line).', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_deferred_js',
		__( '<strong>JS </strong>files with deferred loading:', 'rocket' ),
		'rocket_defered_module',
		'advanced',
		'rocket_display_imp_options'
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
			'type'         => 'checkbox',
			'label'        => __('Enable Content Delivery Network.', 'rocket' ),
			'label_for'    => 'cdn',
			'label_screen' => __( 'CDN:', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_cdn_cnames',
		__( 'Replace site\'s hostname with:', 'rocket' ),
		'rocket_cnames_module',
		'cdn',
		'rocket_display_cdn_options'
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
			'button_label' => __( 'Clear cache', 'rocket' ),
			'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ),
			'description'  => __( 'To purge the cache for the whole site.', 'rocket' )
		)
	);
	add_settings_field(
		'rocket_preload',
		__( 'Preload cache', 'rocket' ),
		'rocket_button',
		'tools',
		'rocket_display_tools',
        array(
        	'button_label' => __( 'Preload cache', 'rocket' ),
        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=preload' ), 'preload' ),
        	'description'  => __( 'Lets ask the passage of the bot to preload the cache (homepage + internal links on this page)', 'rocket' )
        )
    );
	add_settings_field(
		'rocketeer',
		__( 'Support', 'rocket' ),
		'rocket_button',
		'tools',
		'rocket_display_tools',
        array(
        	'button_label' => __( 'Send my configuration', 'rocket' ),
        	'url'		   => wp_nonce_url( admin_url( 'admin-post.php?action=rocketeer' ), 'rocketeer' ),
        	'description'  => __( 'When posting a support request, thank you to click on this button. We receive information regarding your installation to help us understand and solve your problem. The information collected will not be sold or used for other purpose than support.', 'rocket' ) )
    );
?>
	<div class="wrap">
	<div id="icon-rocket" class="icon32"></div>
	<h2>WP Rocket</h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'wp_rocket' ); ?>
		<input type="hidden" name="wp_rocket_settings[secret_cache_key]" value="<?php echo esc_attr( get_rocket_option( 'secret_cache_key' ) ) ;?>" />
		<input type="hidden" name="wp_rocket_settings[minify_key]" value="<?php echo str_replace( '.', '', uniqid( '', true ) ); ?>" />
		<?php submit_button(); ?>
		<h2 class="nav-tab-wrapper hide-if-no-js">
			<?php if( rocket_valid_key() ) : ?>
				<a href="#tab_basic" class="nav-tab"><?php _e( 'Basic options', 'rocket' ); ?></a>
				<a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced options', 'rocket' ); ?></a>
				<a href="#tab_cdn" class="nav-tab">CDN</a>
				<a href="#tab_tools" class="nav-tab"><?php _e( 'Tools', 'rocket' ); ?></a>
				<?php if( WPLANG == 'fr_FR' ) : ?>
					<a href="#tab_tutos" class="nav-tab"><?php _e( 'Tutorials', 'rocket' ); ?></a>
					<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
				<?php endif; ?>

				<a href="#tab_support" class="nav-tab" style="color:#FF0000;"><?php _e( 'Support', 'rocket' ); ?></a>
				<input type="hidden" name="wp_rocket_settings[consumer_key]" value="<?php esc_attr_e( get_rocket_option( 'consumer_key' ) ); ?>" />
			<?php else: ?>
				<a href="#tab_apikey" class="nav-tab"><?php _e( 'API KEY', 'rocket' ); ?></a>
			<?php endif; ?>
		</h2>
		<div id="rockettabs">
			<?php if( !rocket_valid_key() ) : ?>
				<div class="rkt-tab" id="tab_apikey"><?php do_settings_sections( 'apikey' ); ?></div>
			<?php else: ?>
				<div class="rkt-tab" id="tab_basic"><?php do_settings_sections( 'basic' ); ?></div>
				<div class="rkt-tab" id="tab_advanced"><?php do_settings_sections( 'advanced' ); ?></div>
				<div class="rkt-tab" id="tab_cdn"><?php do_settings_sections( 'cdn' ); ?></div>
				<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'tools' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_tutos">
					<?php include( WP_ROCKET_ADMIN_PATH . 'tutorials.php' ); ?>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq">
					<?php include( WP_ROCKET_ADMIN_PATH . 'faq.php' ); ?>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_support">
					<p><?php _e('If none of the FAQ answers, answer your problem, you can tell us your issue on our <a href="http://support.wp-rocket.me/" target="_blank">Support</a>. We will reply as soon as possible.', 'rocket');?></p>
					<p><a href="http://support.wp-rocket.me/" class="button-primary" target="_blank"><?php _e( 'Go to Support', 'rocket' );?></a></p>
			<?php endif; ?>
				</div>
		</div>
		<?php submit_button(); ?>
	</form>
<?php
}



/**
 * Tell to WordPress to be confident with uor setting, we are clean!
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
	if( !$file )
		return false;
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
		return $inputs;


	/*
	 * Option : Purge delay
	 */

	$inputs['purge_cron_interval'] = isset( $inputs['purge_cron_interval'] ) ? (int)$inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );

	$inputs['purge_cron_unit'] = isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );


	/*
	 * Option : Prefetch DNS requests
	 */

	if( isset( $inputs['dns_prefetch'] ) ) :

		$inputs['dns_prefetch'] = array_unique( array_filter( array_map( 'esc_url', array_map( 'trim', explode( "\n", $inputs['dns_prefetch'] ) ) ) ) );

	else :

		$inputs['dns_prefetch'] = array();

	endif;


	/*
	 * Option : Empty the cache of the following pages when updating an article
	 */

	if( isset( $inputs['cache_purge_pages'] ) ) :

		$inputs['cache_purge_pages'] = array_unique( array_filter( array_map( 'rocket_clean_exclude_file', array_map( 'esc_url', 					array_map( 'trim', explode( "\n", $inputs['cache_purge_pages'] ) ) ) ) ) );

	else :

		$inputs['cache_purge_pages'] = array();

	endif;


	/*
	 * Option : Never cache the following pages
	 */

	if( isset( $inputs['cache_reject_uri'] ) ) :

		$inputs['cache_reject_uri'] = array_unique( array_filter( array_map( 'rocket_clean_exclude_file', array_map( 'esc_url', 					array_map( 'trim', explode( "\n", $inputs['cache_reject_uri'] ) ) ) ) ) );

	else :

		$inputs['cache_reject_uri'] = array();

	endif;


	/*
	 * Option : Don't cache pages that use the following cookies
	 */

	if( isset( $inputs['cache_reject_cookies'] ) ) :

		$inputs['cache_reject_cookies'] = array_unique( array_filter( array_map( 'sanitize_key', array_map( 'trim', explode( "\n", $inputs['cache_reject_cookies'] ) ) ) ) );

	else :

		$inputs['cache_reject_cookies'] = array();

	endif;


	/*
	 * Option : CSS files to exclude of the minification
	 */

	if( isset( $inputs['exclude_css'] ) ) :

		$inputs['exclude_css'] = array_unique( array_filter( array_map( 'rocket_sanitize_css', array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_css'] ) ) ) ) ) );

	else :

		$inputs['exclude_css'] = array();

	endif;


	/*
	 * Option : JS files to exclude of the minification
	 */

	if( isset( $inputs['exclude_js'] ) ) :

		$inputs['exclude_js'] = array_unique( array_filter( array_map( 'rocket_sanitize_js', 		array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_js']) ) ) ) ) );

	else :

		$inputs['exclude_js'] = array();

	endif;


	/*
	 * Option : JS files with deferred loading
	 */

	if( isset( $inputs['deferred_js_files'] ) ) :

		$inputs['deferred_js_files'] = array_filter( array_map( 'rocket_sanitize_js', array_unique( $inputs['deferred_js_files'] ) ) );

	else :

		$inputs['deferred_js_files'] = array();

	endif;


	if( !$inputs['deferred_js_files'] )
	{
		$inputs['deferred_js_wait'] = array();
	}
	else
	{

		for( $i=0; $i<=max(array_keys($inputs['deferred_js_files'])); $i++)
		{

			if( !isset( $inputs['deferred_js_files'][$i] ) )
				unset( $inputs['deferred_js_wait'][$i] );
			else
				$inputs['deferred_js_wait'][$i] = isset( $inputs['deferred_js_wait'][$i] ) ? '1' : '0';

		}

		$inputs['deferred_js_files'] = array_values( $inputs['deferred_js_files'] );
		ksort( $inputs['deferred_js_wait'] );
		$inputs['deferred_js_wait'] = array_values( $inputs['deferred_js_wait'] );

	}


	/*
	 * Option : CDN
	 */

	$inputs['cdn_cnames'] = isset( $inputs['cdn_cnames'] ) ? array_unique( array_filter( $inputs['cdn_cnames'] ) ) : array();


	if( !$inputs['cdn_cnames'] )
	{
		$inputs['cdn_zone'] = array();
	}
	else
	{

		for( $i=0; $i<=max(array_keys($inputs['cdn_cnames'])); $i++)
		{

			if( !isset( $inputs['cdn_cnames'][$i] ) )
				unset( $inputs['cdn_zone'][$i] );
			else
				$inputs['cdn_zone'][$i] = isset( $inputs['cdn_zone'][$i] ) ? '1' : '0';

		}

		$inputs['cdn_cnames'] = array_values( $inputs['cdn_cnames'] );
		ksort( $inputs['cdn_zone'] );
		$inputs['cdn_zone'] = array_values( $inputs['cdn_zone'] );

	}


	/*
	 * Option : Consumer Key
	 */

	if( $inputs['consumer_key']==hash( 'crc32', rocket_get_domain( home_url() ) ) )
	{

		$response = wp_remote_get( WP_ROCKET_WEB_VALID, array( 'timeout'=>30 ) );
		if( !is_a($response, 'WP_Error') && strlen( $response['body'] )==32 )
			$inputs['secret_key'] = $response['body'];

	}
	else
	{
		unset( $inputs['secret_key'] );
	}

	rocket_renew_box( 'rocket_warning_logged_users' );
	return $inputs;

}



/**
 * When our settings are saved: purge, flush, preload!
 *
 * @since 1.0
 *
 */

add_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' );
function rocket_after_save_options()
{

	// Purge all cache files
	rocket_clean_domain();

	// Purge all minify cache files
	rocket_clean_minify();

	// Update .htaccess file rules
	flush_rocket_htaccess( !rocket_valid_key() );

	// Update config file
	rocket_generate_config_file();

	// Set COOKIE_DOMAIN constant in wp-config.php
	set_rocket_cookie_domain_define( get_rocket_option( 'cdn', false ) );

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
		wp_clear_scheduled_hook( 'rocket_purge_time_event' );

  }

  return $newvalue;

}