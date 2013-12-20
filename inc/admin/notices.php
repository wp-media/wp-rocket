<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );


/**
 * This warning is displayed to inform the user that a plugin de/activation can be followed by a cache purgation
 *
 * since 1.3.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_plugin_modification' );
function rocket_warning_plugin_modification()
{

	
	if( current_user_can( 'manage_options' ) && rocket_valid_key() ) 
	{
		
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		
		if( !in_array( __FUNCTION__, (array)$boxes ) ) 
		{ ?>

			<div class="updated">
				
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
				
				<p><strong>WP Rocket</strong>: <?php _e( 'One or more extensions have been enabled or disabled, do not forget to clear the cache if necessary.', 'rocket' ) ;?> <a class="wp-core-ui button" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ); ?>"><?php _e('Clear cache', 'rocket') ; ?></a></p>
				
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
		'flexicache/wp-plugin.php'
	);

	if( get_rocket_option( 'lazyload' ) )
	{
		$plugins[] = 'bj-lazy-load/bj-lazy-load.php';
		$plugins[] = 'lazy-load/lazy-load.php';
		$plugins[] = 'jquery-image-lazy-loading/jq_img_lazy_load.php';
	}

	if( get_rocket_option( 'minify_css' ) || get_rocket_option( 'minify_js' ) || get_rocket_option( 'minify_html' ) )
	{
		$plugins[] = 'bwp-minify/bwp-minify.php';
		$plugins[] = 'wp-minify/wp-minify.php';
		$plugins[] = 'wp-html-compression/wp-html-compression.php';
		$plugins[] = 'scripts-gzip/scripts_gzip.php';
		$plugins[] = 'autoptimize/autoptimize.php';
		$plugins[] = 'wp-js/wp-js.php';
	}

	foreach ( $plugins as $plugin )
	{
		if( is_plugin_active( $plugin ) )
			$plugins_to_deactivate[] = $plugin;
	}

	if( current_user_can( 'manage_options' ) 
		&& count( $plugins_to_deactivate ) 
		&& rocket_valid_key() 
	) { ?>

		<div class="error">
			
			<p><strong>WP Rocket</strong>: <?php _e( 'The following plugins are not compatible with WP Rocket and will cause unexpected results:', 'rocket' ); ?></p>
			
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
 * since 1.1.10
 *
 */

add_action( 'admin_notices', 'rocket_warning_logged_users' );
function rocket_warning_logged_users()
{

	global $current_user, $current_screen;
	$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
	
	if( current_user_can( 'manage_options' )
	    && 'settings_page_wprocket' == $current_screen->base
	    && !in_array( __FUNCTION__, (array)$boxes )
	    && !get_rocket_option( 'cache_logged_user' )
	    && rocket_valid_key()
	) { ?>

		<div class="updated">
			
			<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
			
			<p><strong>WP Rocket</strong>: <?php _e( 'Connected users don\'t have the cached version of the website. We recommend you, to browse your website disconnected.', 'rocket' );?></p>
			
		</div>

	<?php
	}
	
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

	if( current_user_can( 'manage_options' )
	    && !$GLOBALS['wp_rewrite']->using_permalinks()
	    && rocket_valid_key()
	) { ?>
		
		<div class="error">
			<p><strong>WP Rocket</strong>: <?php echo sprintf( __( 'A custom permalink structure is required for <strong>WP Rocket</strong> to work properly. Please go to <a href="%s">Permalink</a> to configure them.', 'rocket'), admin_url( '/options-permalink.php' ) ); ?></p>
		</div>
		
	<?php
	}
	
}



/**
 * This warning is displayed when the wp-config.php file isn't writeable
 *
 * since 2.0
 *
 */

add_action( 'admin_notices', 'rocket_warning_wp_config_permissions' );
function rocket_warning_wp_config_permissions()
{
	$config_file =  get_home_path() . 'wp-config.php';

	if( current_user_can( 'manage_options' )
		&& ( !file_exists( $config_file ) || !is_writable( $config_file ) )
	    && ( !defined( 'WP_CACHE' ) || !WP_CACHE )
	    && rocket_valid_key()
	) {
		
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		
		if( !in_array( __FUNCTION__, (array)$boxes ) )
		{ ?>
			
			<div class="error">
				
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
				
				<p><strong>WP Rocket</strong>: <?php echo sprintf( __('If you had <a href="%s" target="_blank">writing permissions</a> on <code>wp-config.php/code> file, <strong>WP Rocket</strong> could do this automatically. This is not the case, so here are the constance  you have to put in your <code>wp-config.php</code> file for <strong>WP Rocket</strong> works correctly.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions' ); ?></p>
				
				<?php
				
				// Get the content of the WP_CACHE constant added by WP Rocket
				$define = "/** Enable Cache */\r\n" . "define('WP_CACHE', 'true'); // Added by WP Rocket\r\n";
				
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

	if( current_user_can( 'manage_options' )
		&& ( !file_exists( $advanced_cache_file ) || !is_writable( $advanced_cache_file ) )
	    && rocket_valid_key()
	) {
		
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		
		if( !in_array( __FUNCTION__, (array)$boxes ) )
		{ ?>
			
			<div class="error">
				
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
				
				<p><strong>WP Rocket</strong>: <?php echo sprintf( __( 'If you had <a href="%s" target="_blank">writing permissions</a> on <code>%s</code> file, <strong>WP Rocket</strong> could do this automatically. This is not the case, so here are the code you have to put in your <code>%s</code> file for <strong>WP Rocket</strong> works correctly.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', basename( WP_CONTENT_DIR ) . '/advanced-cache.php', basename( WP_CONTENT_DIR ) . '/advanced-cache.php' ); ?></p>
				
				<?php
				
				// Get the content of advanced-cache.php file added by WP Rocket
				$config = get_rocket_config_file();
				
				?>
				
				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="8"><?php echo esc_textarea( $config[1] ); ?></textarea></p>
			</div>
			
		<?php
		}
		
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

	if( current_user_can( 'manage_options' )
	    && ( !file_exists( $htaccess_file ) || !is_writable( $htaccess_file ) )
	    && rocket_valid_key()
	) {
		
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		
		if( !in_array( __FUNCTION__, (array)$boxes ) ) 
		{ ?>
			
			<div class="error">
				
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
				
				<p><strong>WP Rocket</strong>: <?php echo sprintf( __( 'If you had <a href="%s" target="_blank">writing permissions</a> on <code>.htaccess</code> file, <strong>WP Rocket</strong> could do this automatically. This is not the case, so here are the rewrite rules you have to put in your <code>.htaccess</code> file for <strong>WP Rocket</strong> works correctly. Click on the field and press Ctrl-A to select all.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions' ); ?></p>
				
				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( get_rocket_htaccess_marker() ); ?></textarea></p>
				
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

	if( current_user_can( 'manage_options' )
	    && ( !is_dir( WP_ROCKET_CACHE_PATH ) || !is_writable( WP_ROCKET_CACHE_PATH ) )
	    && rocket_valid_key()
	) {
		
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		
		if( !in_array( __FUNCTION__, (array)$boxes ) ) 
		{
			?>
			<div class="error">
				
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>" class="rkt-cross"><?php _e('Ignore', 'rocket'); ?></a>
				
				<p><strong>WP Rocket</strong>: <?php echo sprintf ( __('Be careful, you don\'t have <a href="%s" target="_blank">writing permissions</a> on <strong>WP Rocket</strong> cache folder (<code>%s</code>). For <strong>WP Rocket</strong> works properly, please give CHMOD <code>755</code> or <code>775</code> on this folder.', 'rocket' ), 'http://codex.wordpress.org/Changing_File_Permissions', WP_ROCKET_CACHE_PATH ); ?></p>
				
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
	
	<div class="updated hide-if-js">
		<p><strong>WP Rocket</strong> : <?php echo sprintf ( __ ('To finish the install and take advantage of high performance provided by our plugin, thank you to <a href="%s">Enter you API key</a>.', 'rocket' ), admin_url( 'options-general.php?page=wprocket' ) ) ;?></p>
	</div>
	
<?php
}