<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * This warning is display when they are no permalink structure in the configuration.
 *
 * since 1.0
 *
 */

add_action( 'admin_footer', 'rocket_warning_using_permalinks' );
function rocket_warning_using_permalinks()
{

	if( $GLOBALS['wp_rewrite']->using_permalinks() )
		return false; ?>

	<div class="error">
		<p><strong>WP Rocket</strong> : Une structure de permalien personnalisé est requis pour que <strong>WP Rocket</strong> pour fonctionne correctement. S'il vous plaît, aller à la page <a href="<?php echo admin_url( '/options-permalink.php' ); ?>">Permaliens</a> pour configurer vos permaliens.</p>
	</div>

	<?php
}


/**
 * This warning is display when the .htaccess file isn't exist or isn't writeable 
 *
 * since 1.0
 *
 */

add_action( 'admin_footer', 'rocket_warning_htaccess_permissions' );
function rocket_warning_htaccess_permissions()
{

	$htaccess_file = ABSPATH . '.htaccess';

	if( !file_exists( $htaccess_file ) || !is_writeable( $htaccess_file ) )
	{ ?>

		<div class="error">
			<p><strong>WP Rocket</strong> : Si vous aviez les <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">droits en écriture (en)</a> sur le fichier <code>.htaccess</code>, <strong>WP Rocket</strong> pourrait faire cela automatiquement. Ce n’est pas le cas, donc voici les règles de réécriture que vous devrez mettre dans votre fichier <code>.htaccess</code> pour que le <strong>WP Rocket</strong> fonctionne correctement. Cliquez sur le champ et appuyez sur Ctrl-a pour tout sélectionner.</p>
			<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( get_rocket_htaccess_marker() ); ?></textarea></p>
		</div>

	<?php
	}

}



/**
 * This warning is display when the .htaccess file isn't exist or isn't writeable 
 *
 * since 1.0
 *
 */
 
add_action( 'admin_footer', 'rocket_warning_cache_dir_permissions' );
function rocket_warning_cache_dir_permissions() 
{
	
	if( !is_dir( WP_ROCKET_CACHE_PATH ) || !is_writeable( WP_ROCKET_CACHE_PATH ) )
	{ ?>
		
		<div class="error">
			<p><strong>WP Rocket</strong> : Attention, vous n'avez pas les <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">droits en écriture (en)</a> sur le dossier de cache de <strong>WP Rocket</strong> (<code><?php echo WP_ROCKET_CACHE_PATH; ?></code>). Pour que <strong>WP Rocket</strong> fonctionne correctement, veuillez indiquer un CHMOD de <code>755</code> ou de <code>775</code> sur ce dossier.</p>
		</div>
		
	<?php
	}
		
}



// Need API key
function rocket_need_api_key()
{

	?>
	<div class="updated">
		<p><strong>WP Rocket</strong> : Pour finaliser l'installation et profiter des performances apportées par notre plugin, merci de <a href="<?php echo admin_url( 'options-general.php?page=wprocket' ); ?>">renseigner votre clé API</a>.</p>
	</div>
	<?php
}