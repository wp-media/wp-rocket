<?php

/**
 * Add submenu in menu "Settings"
 *
 * Since 1.0
 *
 */

add_action( 'admin_menu', create_function( "", "add_options_page( 'WP Rocket', 'WP Rocket', 'manage_options', 'wprocket', 'rocket_display_options');" ) );
function rocket_display_options()
{

	$options = get_option( 'wp_rocket_settings' );
	?>

	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>WP Rocket</h2>

		<h3>Options générales</h3>
		<form method="post" action="options.php">

		    <table class="form-table">
			    <tr>
				    <th scope="row">
				    	Chargement différé des images :
				    	<br/>
				    	<span class="description">(Lazyload)</span>
				    </th>
				    <td valign="top">
				        <fieldset>
				            <legend class="screen-reader-text"><span>Chargement différé des images</span></legend>
				            <label for="lazyload">
				                <input type="checkbox" <?php echo isset( $options['lazyload'] ) ? checked( $options['lazyload'], 1, false ) : ''; ?> value="1" id="lazyload" name="wp_rocket_settings[lazyload]">
				                <p class="description">Le LazyLoad (ou chargement différé des images) consiste à afficher les images d’une page uniquement quand elles sont visibles par l’internaute.
				                <br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.</p>
				            </label>
				        </fieldset>
				    </td>
				</tr>
				<tr>
				    <th scope="row">
				    	Optimisation des fichiers :
				    	<br/>
				    	<span class="description">(Minification & Concaténation)</span>
				    </th>
				    <td valign="top">
				        <fieldset>
				            <legend class="screen-reader-text"><span>Minification des fichiers</span></legend>
				            <label for="minify_css">
				                <input type="checkbox" <?php echo isset( $options['minify_css'] ) ? checked( $options['minify_css'], 1, false ) : ''; ?> value="1" id="minify_css" name="wp_rocket_settings[minify_css]"> CSS
				            </label>
				            <br/>
				            <label for="minify_js">
				                <input type="checkbox" <?php echo isset( $options['minify_js'] ) ? checked( $options['minify_js'], 1, false ) : ''; ?> value="1" id="minify_js" name="wp_rocket_settings[minify_js]"> JavaScript

				            </label>
				            <p class="description">La minification supprime tout espace, retour à la ligne et commentaires présents dans les fichiers CSS et JavaScript.
				            <br/>Ce mécanisme réduit le poids de chaque fichier et permet une lecture plus rapide par les navigateurs et les moteurs de recherche.</p>

							<p class="description">La concaténation combine tous les fichiers CSS et JavaScript afin de n’en faire plus qu’un seul par type.
							<br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.</p>

							<p class="description">Attention : la concaténation des fichiers peut générer des problèmes d’affichages, <strong>en cas d’erreur nous vous conseillons de le désactiver</strong>.</p>
				        </fieldset>
				    </td>
				</tr>
				<tr>
				    <th scope="row">Mobile :</th>
				    <td valign="top">
				        <fieldset>
				            <legend class="screen-reader-text"><span>Mobile</span></legend>
				            <label for="cache_mobile">
				                <input type="checkbox" <?php echo isset( $options['cache_mobile'] ) ? checked( $options['cache_mobile'], 1, false ) : ''; ?> value="1" id="cache_mobile" name="wp_rocket_settings[cache_mobile]"> Désactiver la mise en cache pour les appareils mobile.
				                <p class="description"></p>
				            </label>
				        </fieldset>
				    </td>
				</tr>
		    </table>

			<h3>Options avancées</h3>

			<table class="form-table">
				<tr>
				    <th scope="row">
				        <label for="purge_cron_interval">Délai de Purge :</label>
				    </th>
				    <td>
				        <input type="text" value="<?php echo $options['purge_cron_interval']; ?>" id="purge_cron_interval" name="wp_rocket_settings[purge_cron_interval]"> secondes
				        <p class="description">Vous pouvez spécifier une durée de vie pour les fichiers de cache.
						<br/>Indiquez 0 pour une durée de vie illimitée.</p>
				    </td>
				</tr>
				<tr>
				    <th scope="row">
				        <label for="cache_reject_uri">Ne jamais mettre en cache les pages suivantes :</label>
				    </th>

				    <td>
				        <textarea id="cache_reject_uri" name="wp_rocket_settings[cache_reject_uri]" cols="50" rows="5"><?php echo esc_textarea( implode( "\n" , (array)$options['cache_reject_uri'] ) ); ?></textarea>
				        <p class="description">Indiquez l'URL des pages à rejeter (une par ligne).
					        <br/>
					        Il est possible d'utiliser des expressions régulières (REGEX).
				        </p>
				    </td>
				</tr>
				<tr>
				    <th scope="row">
				        <label for="cache_reject_cookies">Ne jamais mettre en cache les pages qui utilisent les cookies suivants :</label>
				    </th>

				    <td>
				        <textarea id="cache_reject_cookies" name="wp_rocket_settings[cache_reject_cookies]" cols="50" rows="5"><?php echo esc_textarea( implode( "\n" , (array)$options['cache_reject_cookies'] ) ); ?></textarea>
				        <p class="description">Indiquez les noms des cookies à rejeter (un par ligne)</p>
				    </td>
				</tr>
				<tr>
					<td colspan="2"> <p class="description" style="width:595px;">Pour des raisons de compatibilité, vous pouvez indiquer ci-dessous des fichiers <strong>CSS</strong> ou <strong>JavaScript</strong> à exclure du mécanisme de minification/concaténation.</p></td>
				</tr>
				<tr>
				    <th scope="row">
				        <label for="exclude_css">Fichiers <strong>CSS</strong> à exclure lors de la minification :</label>
				    </th>
				    <td>
				        <textarea id="exclude_css" name="wp_rocket_settings[exclude_css]" cols="50" rows="5"><?php echo esc_textarea( implode( "\n" , (array)$options['exclude_css'] ) ); ?></textarea>
				        <p class="description">Indiquez l'URL des fichiers <strong>CSS</strong> à rejeter (un par ligne)</p>
				    </td>
				</tr>
				<tr>
				    <th scope="row">
				        <label for="exclude_js">Fichiers <strong>JavaScript</strong> à exclure lors de la minification :</label>
				    </th>
				    <td>
				        <textarea id="exclude_js" name="wp_rocket_settings[exclude_js]" cols="50" rows="5"><?php echo esc_textarea( implode( "\n" , (array)$options['exclude_js'] ) ); ?></textarea>
				        <p class="description">Indiquez l'URL des fichiers <strong>JavaScript</strong> à rejeter (un par ligne)</p>
				    </td>
				</tr>
			</table>

		    <?php

		    	// Add fields
		    	settings_fields( 'wp_rocket' );

				// Add submit button
				submit_button();
		    ?>

		</form>
	</div>

<?php
}



/**
 * Lien vers la page de configuration du plugin
 *
 * Since 1.0
 *
 */

add_filter( 'plugin_action_links_wp-rocket/wp-rocket.php', 'rocket_settings_action_links' );
function rocket_settings_action_links( $links )
{
    array_unshift( $links, '<a href="' . admin_url( 'options-general.php?page=wprocket' ) . '">' . __( 'Settings' ) . '</a>' );
    return $links;
}



/**
 * TO DO - Description
 *
 * Since 1.0
 *
 */

add_action('admin_init', create_function("", "register_setting( 'wp_rocket', 'wp_rocket_settings', 'wp_rocket_settings_callback' );") );
function wp_rocket_settings_callback( $inputs )
{

	// Clean and register exclude CSS and JS files in a array
	$inputs['cache_reject_uri'] = array_filter( array_map( 'clean_exclude_file', explode( "\n", trim($_POST['wp_rocket_settings']['cache_reject_uri']) ) ) );
	$inputs['cache_reject_cookies'] = array_filter(array_map( 'clean_exclude_file', explode( "\n", trim($_POST['wp_rocket_settings']['cache_reject_cookies']) ) ) );
	$inputs['exclude_css'] = array_filter( array_map( 'clean_exclude_file', explode( "\n", trim($_POST['wp_rocket_settings']['exclude_css']) ) ) );
	$inputs['exclude_js'] = array_filter( array_map( 'clean_exclude_file', explode( "\n", trim($_POST['wp_rocket_settings']['exclude_js']) ) ) );


	//
	$inputs['purge_cron_interval'] = isset( $_POST['wp_rocket_settings']['purge_cron_interval'] ) ? (int)$_POST['wp_rocket_settings']['purge_cron_interval'] : 0;

	return $inputs;
}



/**
 * TO DO - Description
 *
 * Since 1.0
 *
 */

add_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' );
function rocket_after_save_options()
{

	// Purge all cache files when user save options
	rocket_clean_domain();


	// Clear cron
	wp_clear_scheduled_hook( 'rocket_purge_time_event' );


	//
	flush_rocket_htaccess();
}



function clean_exclude_file( $file )
{

	// Get relative url
    $file = str_replace( home_url( '/' ), '', $file );
	$file = preg_replace( '#\?.*$#', '', $file );
	$file = trim( $file );

	return $file;

}