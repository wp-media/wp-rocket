<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * TO DO - Description
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
		<p>Une structure de permalien personnalisé est requis pour que <strong>WP Rocket</strong> pour fonctionne correctement. S'il vous plaît, aller à la page <a href="<?php echo admin_url( '/options-permalink.php' ); ?>">Permaliens</a> pour configurer vos permaliens.</p>
	</div>

	<?php
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

add_action( 'admin_footer', 'rocket_warning_htaccess_permissions' ); //// footer, pas admin_notices
function rocket_warning_htaccess_permissions()
{

	$htaccess_file = ABSPATH . '.htaccess';

	if( !file_exists( $htaccess_file ) || !is_writeable( $htaccess_file ) )
	{ ?>

		<div class="error">
			<p>Si vous aviez les <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">droits en écriture (en)</a> sur le fichier <code>.htaccess</code>, <strong>WP Rocket</strong> pourrait faire cela automatiquement. Ce n’est pas le cas, donc voici les règles de réécriture que vous devrez mettre dans votre fichier <code>.htaccess</code> pour que le <strong>WP Rocket</strong> fonctionne correctement. Cliquez sur le champ et appuyez sur Ctrl-a pour tout sélectionner.</p>
			<p><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( get_rocket_htaccess_marker() ); ?></textarea></p>
		</div>

	<?php
	}

}