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

	global $current_user;
	if( current_user_can( 'manage_options' ) && rocket_valid_key() ) {
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		if( !in_array( __FUNCTION__, (array)$boxes ) ) { ?>

			<div class="updated">
				<span class="rocket_cross"><a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>"><img src="<?php echo admin_url( '/images/no.png' ); ?>" title="Ignorer jusqu'à la prochaine fois" alt="Ignorer" /></a></span>
				<p><strong>WP Rocket</strong> : Une ou plusieurs extensions ont été activées ou désactivées, n'oubliez pas de vider le cache si nécessaire. <a class="wp-core-ui button" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ); ?>">Vider le cache</a></p>
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
		'wp-fast-cache/wp-fast-cache.php'
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
	}

	foreach ( $plugins as $plugin )
	{
		if( is_plugin_active( $plugin ) )
			$plugins_to_deactivate[] = $plugin;
	}

	if( current_user_can( 'manage_options' ) && count( $plugins_to_deactivate ) && rocket_valid_key() )
	{ ?>

		<div class="error">
			<p><strong>WP Rocket</strong> : <?php _e( 'Les plugins suivants ne sont pas compatibles avec WP Rocket et vont entraîner des résultats inattendus :', 'rocket' ); ?></p>
			<ul class="rocket-plugins-error">
			<?php
			foreach ( $plugins_to_deactivate as $plugin )
			{

				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . DIRECTORY_SEPARATOR . $plugin);
				echo '<li>' . $plugin_data['Name'] . '</span> <a href="' . wp_nonce_url( admin_url( 'admin-post.php?action=deactivate_plugin&plugin=' . urlencode($plugin) ), 'deactivate_plugin' ) . '" class="button-secondary alignright">' . __( 'Désactiver', 'rocket' ) . '</a></li>';

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
	if( current_user_can( 'manage_options' ) && 'settings_page_wprocket'==$current_screen->base && !in_array( __FUNCTION__, (array)$boxes ) && rocket_valid_key() ) { ?>

		<div class="updated">
			<span class="rocket_cross"><a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>"><img src="<?php echo admin_url( '/images/no.png' ); ?>" title="Ignorer jusqu'à la prochaine fois" alt="Ignorer" /></a></span>
			<p><strong>WP Rocket</strong> : Pour rappel, les utilisateurs connectés n'ont pas la version du site en cache. Nous vous conseillons de naviguer sur le site en étant déconnecté pour être en situation réelle.</p>
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

	if( current_user_can( 'manage_options' ) && !$GLOBALS['wp_rewrite']->using_permalinks() && rocket_valid_key() )
	{
	?>
		<div class="error">
			<p><strong>WP Rocket</strong> : Une structure de permalien personnalisé est requis pour que <strong>WP Rocket</strong> pour fonctionne correctement. S'il vous plaît, aller à la page <a href="<?php echo admin_url( '/options-permalink.php' ); ?>">Permaliens</a> pour configurer vos permaliens.</p>
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
	$htaccess_file = get_real_file_to_edit( '.htaccess' );

	if( current_user_can( 'manage_options' ) && ( !file_exists( $htaccess_file ) || !is_writable( $htaccess_file ) ) && rocket_valid_key() )
	{
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		if( !in_array( __FUNCTION__, (array)$boxes ) ) {
			?>
			<div class="error">
				<span class="rocket_cross"><a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>"><img src="<?php echo admin_url( '/images/no.png' ); ?>" title="Ignorer jusqu'à la prochaine fois" alt="Ignorer" /></a></span>
				<p><strong>WP Rocket</strong> : Si vous aviez les <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">droits en écriture (en)</a> sur le fichier <code>.htaccess</code>, <strong>WP Rocket</strong> pourrait faire cela automatiquement. Ce n’est pas le cas, donc voici les règles de réécriture que vous devrez mettre dans votre fichier <code>.htaccess</code> pour que le <strong>WP Rocket</strong> fonctionne correctement. Cliquez sur le champ et appuyez sur Ctrl-a pour tout sélectionner.</p>
				<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( get_rocket_htaccess_marker() ); ?></textarea></p>
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

add_action( 'admin_notices', 'rocket_warning_cache_dir_permissions' );
function rocket_warning_cache_dir_permissions()
{

	if( current_user_can( 'manage_options' ) && ( !is_dir( WP_ROCKET_CACHE_PATH ) || !is_writable( WP_ROCKET_CACHE_PATH ) ) && rocket_valid_key() )
	{
		global $current_user;
		$boxes = get_user_meta( $current_user->ID, 'rocket_boxes', true );
		if( !in_array( __FUNCTION__, (array)$boxes ) ) {
			?>
			<div class="error">
				<span class="rocket_cross"><a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box='.__FUNCTION__ ), 'rocket_ignore_'.__FUNCTION__ ); ?>"><img src="<?php echo admin_url( '/images/no.png' ); ?>" title="Ignorer jusqu'à la prochaine fois" alt="Ignorer" /></a></span>
				<p><strong>WP Rocket</strong> : Attention, vous n'avez pas les <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">droits en écriture (en)</a> sur le dossier de cache de <strong>WP Rocket</strong> (<code><?php echo WP_ROCKET_CACHE_PATH; ?></code>). Pour que <strong>WP Rocket</strong> fonctionne correctement, veuillez indiquer un CHMOD de <code>755</code> ou de <code>775</code> sur ce dossier.</p>
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
{
	?>
	<div class="updated hide-if-js">
		<p><strong>WP Rocket</strong> : Pour finaliser l'installation et profiter des performances apportées par notre plugin, merci de <a href="<?php echo admin_url( 'options-general.php?page=wprocket' ); ?>">renseigner votre clé API</a>.</p>
	</div>
	<?php
}