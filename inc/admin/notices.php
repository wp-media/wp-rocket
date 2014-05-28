<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * This warnings are displayed when the plugin can not be deactivated correctly
 *
 * since 2.0.0
 *
 */

add_action( 'admin_notices', 'rocket_bad_deactivations' );
function rocket_bad_deactivations()
{

	global $current_user;
	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && $msgs = get_transient( $current_user->ID . '_donotdeactivaterocket' ) )
	{

		delete_transient( $current_user->ID . '_donotdeactivaterocket' );
		$errors = array();
		?>

		<div class="error">

			<?php
			foreach( $msgs as $msg)
			{

				switch( $msg ) {

					case 'wpconfig' :

						$errors['wpconfig'] = 	'<p>' . sprintf( __( '<b>%s</b> can not be deactivated because of <code>%s</code>.', 'rocket' ), WP_ROCKET_PLUGIN_NAME, 'WP_CACHE' ) . '<br>' . __( 'This constant is still defined in <code>wp-config.php</code> file and its value must be set to <code>false</code>.', 'rocket' ) . ' ' . sprintf( __( 'Maybe we do not have the write rights on <code>%s</code>.', 'rocket' ), 'wp-config.php' ) . '<br>' . __( 'Please give us rigths or resolve the problem yourself. Then retry deactivation.', 'rocket' ) . '</p>';

					break;

					case 'htaccess' :

						$errors['htaccess'] = '<p>' . sprintf( __( '<b>%s</b> can not be deactivated because of <code>%s</code>.', 'rocket' ), WP_ROCKET_PLUGIN_NAME, '.htaccess' ) . '<br>' . __( 'This file is not writable and we can not remove these directives.', 'rocket' ) . ' ' . sprintf( __( 'Maybe we do not have the write rights on <code>%s</code>.', 'rocket' ), '.htaccess' ) . '<br>' . __( 'Please give us rigths or resolve the problem yourself. Then retry deactivation.', 'rocket' ) . '</p>';

					break;

				}

				$errors = apply_filters( 'rocket_bad_deactivations', $errors, $msg );

			}

			// Display errors
			if( count( $errors ) )
			{

				array_map( 'printf', $errors );

			}

			// We add a link to permit "force deactivation", use at your own risks.
			if( apply_filters( 'rocket_permit_force_deactivation', true ) )
			{
				global $status, $page, $s;
				$plugin_file = 'wp-rocket/wp-rocket.php';
				$rocket_nonce = wp_create_nonce( 'force_deactivation' );

				echo '<p><a href="'.wp_nonce_url('plugins.php?action=deactivate&amp;rocket_nonce=' . $rocket_nonce . '&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file).'">' . __( 'You can still force the deactivation by clicking here.', 'rocket' ) . '</a></p>';
			}
			?>

		</div>

	<?php
	}

}

/**
 * This warning is displayed to inform the user that a plugin de/activation can be followed by a cache purgation
 *
 * since 1.3.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_plugin_modification' );
function rocket_warning_plugin_modification()
{

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) ) && rocket_valid_key() )
	{

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{ ?>

			<div class="updated">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><?php printf( __( '<b>%s</b>: One or more extensions have been enabled or disabled, do not forget to clear the cache if necessary.', 'rocket' ), WP_ROCKET_PLUGIN_NAME ); ?> <a class="wp-core-ui button" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ); ?>"><?php _e('Clear cache', 'rocket') ; ?></a></p>

			</div>

			<?php
		}

	}

}



/**
 * This warning is displayed when some plugins may conflict with WP Rocket
 *
 * since 1.3.0
 *
 */

add_action( 'admin_notices', 'rocket_plugins_to_deactivate' );
function rocket_plugins_to_deactivate()
{

	$plugins_to_deactivate = array();

	// Deactivate all plugins who can cause conflicts with WP Rocket
	$plugins = array(
		'w3-total-cache/w3-total-cache.php',
		'wp-super-cache/wp-cache.php',
		'quick-cache/quick-cache.php',
		'hyper-cache/plugin.php',
		'hyper-cache-extended/plugin.php',
		'wp-fast-cache/wp-fast-cache.php',
		'flexicache/wp-plugin.php',
		'wp-fastest-cache/wpFastestCache.php',
		'gator-cache/gator-cache.php',
		'wp-http-compression/wp-http-compression.php'
	);

	if( get_rocket_option( 'lazyload' ) )
	{
		$plugins[] = 'bj-lazy-load/bj-lazy-load.php';
		$plugins[] = 'lazy-load/lazy-load.php';
		$plugins[] = 'jquery-image-lazy-loading/jq_img_lazy_load.php';
		$plugins[] = 'advanced-lazy-load/advanced_lazyload.php';
	}

	if( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) || get_rocket_option( 'minify_html' ) )
	{
		$plugins[] = 'bwp-minify/bwp-minify.php';
		$plugins[] = 'wp-minify/wp-minify.php';
		$plugins[] = 'wp-html-compression/wp-html-compression.php';
		$plugins[] = 'wp-compress-html/wp_compress_html.php';
		$plugins[] = 'scripts-gzip/scripts_gzip.php';
		$plugins[] = 'autoptimize/autoptimize.php';
		$plugins[] = 'wp-js/wp-js.php';
		$plugins[] = 'minqueue/plugin.php';
	}

	foreach ( $plugins as $plugin )
	{
		if( is_plugin_active( $plugin ) )
			$plugins_to_deactivate[] = $plugin;
	}

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& count( $plugins_to_deactivate )
		&& rocket_valid_key()
	) { ?>

		<div class="error">

			<p><?php printf( __( '<b>%s</b>: The following plugins are not compatible with this plugin and may cause unexpected results:', 'rocket' ), WP_ROCKET_PLUGIN_NAME ); ?></p>

			<ul class="rocket-plugins-error">
			<?php
			foreach ( $plugins_to_deactivate as $plugin )
			{

				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin);
				echo '<li>' . $plugin_data['Name'] . '</span> <a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=deactivate_plugin&plugin=' . urlencode($plugin) ), 'deactivate_plugin' ) . '" class="button-secondary alignright">' . __( 'Desactivate', 'rocket' ) . '</a></li>';

			}
			?>
			</ul>

		</div>

	<?php
	}

}



/**
 * This warning is displayed to inform the user that the plugin can not be tested in connected mode
 *
 * @since 1.1.10
 * @since 2.2 Only returns a string on demand, no more hook
 *
 */

function rocket_warning_logged_users()
{

	return	'</strong><b>' . WP_ROCKET_PLUGIN_NAME . '</b>: ' .
			__( 'Connected users don\'t have the cached version of the website. We recommend you, to browse your website disconnected.', 'rocket' );

}



/**
 * This warning is displayed when there is no permalink structure in the configuration.
 *
 * since 1.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_using_permalinks' );
function rocket_warning_using_permalinks()
{

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
	    && ! $GLOBALS['wp_rewrite']->using_permalinks()
	    && rocket_valid_key()
	) { ?>

		<div class="error">
			<p><?php printf( __( '<b>%s</b>: A custom permalink structure is required to work properly. Please go to <a href="%s">Permalink</a> to configure them.', 'rocket'), WP_ROCKET_PLUGIN_NAME, admin_url( 'options-permalink.php' ) ); ?></p>
		</div>

	<?php
	}

}



/**
 * This warning is displayed when the wp-config.php file isn't writable
 *
 * since 2.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_wp_config_permissions' );
function rocket_warning_wp_config_permissions()
{
	$config_file = rocket_find_wpconfig_path();

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! is_writable( $config_file ) || ! defined( 'WP_CACHE' ) || ! WP_CACHE )
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{ ?>

			<div class="error">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p>
				<?php
					printf( __( '<b>%s</b>: It seems we don\'t have <a href="%s" target="_blank">writing permissions</a> on <code>wp-config.php</code> file or the value of the constant <code>WP_CACHE</code> is set to <code>false</code>', 'rocket'), WP_ROCKET_PLUGIN_NAME, "http://codex.wordpress.org/Changing_File_Permissions" );
					echo '<br>';
					_e( 'To fix this you have to give write rights on <code>wp-config.php</code> and then save again this settings.', 'rocket' );
					echo '<br>';
					_e( 'If the message persists, you have to put this following code in your <code>wp-config.php</code> file so that it works correctly. Click on the field and press Ctrl-A to select all.', 'rocket' );
				?>
				</p>

				<?php

				// Get the content of the WP_CACHE constant added by WP Rocket
				$define = "/** Enable Cache by WP Rocket */\r\ndefine( 'WP_CACHE', true );\r\n";

				?>

				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="2"><?php echo esc_textarea( $define ); ?></textarea></p>
			</div>

		<?php
		}

	}

}



/**
 * This warning is displayed when the advanced-cache.php file isn't writeable
 *
 * since 2.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_advanced_cache_permissions' );
function rocket_warning_advanced_cache_permissions()
{
	$advanced_cache_file =  WP_CONTENT_DIR . '/advanced-cache.php';

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ( ! is_writable( $advanced_cache_file ) )
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{ ?>

			<div class="error">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __( 'If you had <a href="%1$s" target="_blank">writing permissions</a> on <code>%2$s</code> file, <b>%3$s</b> could do this automatically. This is not the case, here is the code you should add in your <code>%2$s</code> file for <b>%3$s</b> to work properly.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', basename( WP_CONTENT_DIR ) . '/advanced-cache.php', WP_ROCKET_PLUGIN_NAME ); ?></p>

				<?php

				// Get the content of advanced-cache.php file added by WP Rocket
				$content = get_rocket_advanced_cache_file();

				?>

				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="8"><?php echo esc_textarea( $content ); ?></textarea></p>
			</div>

		<?php
		}

	}

}



/**
 * This warning is displayed when the advanced-cache.php file isn't ours
 *
 * since 2.2
 *
 */

add_action( 'admin_notices', 'rocket_warning_advanced_cache_not_ours' );
function rocket_warning_advanced_cache_not_ours()
{

	if ( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
		&& ! defined( 'WP_ROCKET_ADVANCED_CACHE' )
		&& get_rocket_option( 'version' ) == WP_ROCKET_VERSION
	    && rocket_valid_key()
	) {

		?>

			<div class="error">

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __( 'It seems that the <code>%s</code> file is not ours. Save the settings, we will automatically recreate the correct one. If it\'s still not working, please delete it and save again.', 'rocket' ), basename( WP_CONTENT_DIR ) . '/advanced-cache.php' ); ?></p>

			</div>

		<?php
	}

}



/**
 * This warning is displayed when the .htaccess file doesn't exist or isn't writeable
 *
 * since 1.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_htaccess_permissions' );
function rocket_warning_htaccess_permissions()
{
	$htaccess_file =  get_home_path() . '.htaccess';

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
	    && ( ! is_writable( $htaccess_file ) )
	    && $GLOBALS['is_apache']
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{ ?>

			<div class="error">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __( 'If you had <a href="%1$s" target="_blank">writing permissions</a> on <code>.htaccess</code> file, <b>%2$s</b> could do this automatically. This is not the case, so here are the rewrite rules you have to put in your <code>.htaccess</code> file for <b>%2$s</b> to work correctly. Click on the field and press Ctrl-A to select all.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', WP_ROCKET_PLUGIN_NAME ) . '<br>' . __('<strong>Warning:</strong> This message will popup again and its content may be updated when saving the options', 'rocket'); ?></p>

				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( get_rocket_htaccess_marker() ); ?></textarea></p>

			</div>

		<?php
		}

	}

}



/**
 * This warning is displayed when the config dir isn't writeable
 *
 * since 2.0.2
 *
 */

add_action( 'admin_notices', 'rocket_warning_config_dir_permissions' );
function rocket_warning_config_dir_permissions()
{

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
	    && ( ! is_writable( WP_ROCKET_CONFIG_PATH ) )
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{
			?>
			<div class="error">
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __('Be careful, you don\'t have <a href="%1$s" target="_blank">writing permissions</a> on <b>%3$s</b> domain configuration folder (<code>%2$s</code>). To make <b>%3$s</b> work properly, please CHMOD <code>755</code> or <code>775</code> or <code>777</code> this folder.<br/>When the problem is solved, thank you to save the %3$s options to generate the configuration file.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', trim( str_replace( ABSPATH, '', WP_ROCKET_CONFIG_PATH ), '/' ), WP_ROCKET_PLUGIN_NAME ); ?></p>

			</div>

		<?php
		}

	}

}



/**
 * This warning is displayed when the cache dir isn't writeable
 *
 * since 1.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_cache_dir_permissions' );
function rocket_warning_cache_dir_permissions()
{

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
	    && ( ! is_writable( WP_ROCKET_CACHE_PATH ) )
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{
			?>
			<div class="error">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __('Be careful, you don\'t have <a href="%1$s" target="_blank">writing permissions</a> on <b>%3$s</b> cache folder (<code>%2$s</code>). For <b>%3$s</b> works properly, please CHMOD <code>755</code> or <code>775</code> or <code>777</code> this folder.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', trim( str_replace( ABSPATH, '', WP_ROCKET_CACHE_PATH ), '/' ), WP_ROCKET_PLUGIN_NAME ); ?></p>

			</div>

		<?php
		}

	}

}



/**
 * This thankful message is displayed when the site has been added
 *
*
 * @since 2.2 On demand display, no hook
 *
 */

function rocket_thank_you_license()
{

	return 	'</strong><b>' . WP_ROCKET_PLUGIN_NAME . '</b>:
			' . __( 'Thank you. Your license has been validated by our servers for you.', 'rocket' ) . '
			<br>
			' . sprintf( __( 'Key: <code>%s</code><br>Email: <i>%s</i>', 'rocket' ), get_rocket_option( 'consumer_key' ), get_rocket_option( 'consumer_email' ) );
}



/**
 * This warning is displayed when the minify cache dir isn't writeable
 *
 * since 2.1
 *
 */

add_action( 'admin_notices', 'rocket_warning_minify_cache_dir_permissions' );
function rocket_warning_minify_cache_dir_permissions()
{

	if( current_user_can( apply_filters( 'rocket_capacity', 'manage_options' ) )
	    && ( ! is_writable( WP_ROCKET_MINIFY_CACHE_PATH ) )
	    && ( get_rocket_option( 'minify_css', false ) || get_rocket_option( 'minify_js', false ) )
	    && rocket_valid_key()
	) {

		$boxes = get_user_meta( $GLOBALS['current_user']->ID, 'rocket_boxes', true );

		if( ! in_array( __FUNCTION__, (array) $boxes ) )
		{
			?>
			<div class="error">

				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><div class="dashicons dashicons-no"></div></a>

				<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b>: <?php printf( __('Be careful, you don\'t have <a href="%1$s" target="_blank">writing permissions</a> on <b>%3$s</b> minify cache folder (<code>%2$s</code>). To make <b>%3$s</b> work properly, please CHMOD <code>755</code> or <code>775</code> or <code>777</code> this folder.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', trim( str_replace( ABSPATH, '', WP_ROCKET_MINIFY_CACHE_PATH ), '/' ), WP_ROCKET_PLUGIN_NAME ); ?></p>

			</div>

		<?php
		}

	}

}



/**
 * This warning is displayed when the API KEY isn't already set or not valid
 *
 * since 1.0
 *
 */

function rocket_need_api_key()
{ ?>

	<div class="updated">
		<p><b><?php echo WP_ROCKET_PLUGIN_NAME; ?></b> : <?php echo sprintf ( __ ('To finish the install and take advantage of high performance provided by our plugin, thank you to <a href="%s">Enter you API key</a>.', 'rocket' ), admin_url( 'options-general.php?page='.WP_ROCKET_PLUGIN_SLUG ) ) ;?></p>
	</div>

<?php
}