<?php
defined( 'ABSPATH' ) or	die( 'Cheatin\' uh?' );

/**
 * Add submenu in menu "Settings"
 *
 * Since 1.0
 *
 */

add_action( 'admin_menu', 'rocket_admin_menu' );
function rocket_admin_menu()
{
	$options = get_option( WP_ROCKET_SLUG );
	add_options_page( 'WP Rocket', 'WP Rocket', 'manage_options', 'wprocket', 'rocket_display_options' );
}



/**
 * Used to display fields on settings form
 *
 * Since 1.0
 *
 */

function rocket_field( $args )
{
	$options = get_option( WP_ROCKET_SLUG );
	if( !is_array( reset( $args ) ) )
		$args = array( $args );
	$full = $args;
	foreach ($full as $args) {
		$args['name'] = isset( $args['name'] ) ? $args['name'] : $args['label_for'];
		$description = isset( $args['description'] ) ? '<p class="description">'.$args['description'].'</p>' : '';
		$placeholder = isset( $args['placeholder'] ) ? 'placeholder="'. $args['placeholder'].'" ' : '';
		$label = isset( $args['label'] ) ? $args['label'] : '';
		$readonly = $args['name'] == 'consumer_key' && rocket_valid_key() ? ' readonly="readonly"' : '';
		if( !isset( $args['fieldset'] ) || $args['fieldset']=='start' )
			echo '<fieldset>';
		switch( $args['type'] ){
			case 'number' :
			case 'text' :
				$value = isset( $options[$args['name']] ) ? esc_attr( $options[$args['name']] ) : '';
				$number_options = $args['type']=='number' ? ' min="0" class="small-text"' : '';
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>
				<?php
				if( $args['name'] == 'consumer_key' )
					if( !rocket_valid_key() ){
					echo '<span style="font-weight:bold;color:red">Clé non valide</span>';
					}else{
						echo '<span style="font-weight:bold;color:green">Clé valide</span>';
					}
				echo $description;
			break;
			case 'textarea' :
				$value = !empty( $options[$args['name']] ) ? esc_textarea( implode( "\n" , $options[$args['name']] ) ) : '';
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="50" rows="5"><?php echo $value; ?></textarea>
					</label>
					<?php echo $description; ?>
				<?php
			break;
			case 'checkbox' :
				$checked = isset( $options[$args['name']] ) ? checked( $options[$args['name']], 1, false ) : '';
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="checkbox" id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1" <?php echo $checked; ?>/> <?php echo $args['label']; ?>
					</label>
					<?php echo $description; ?>
				<?php
			break;
			case 'select' :
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label>	<select id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]">
							<?php foreach( $args['options'] as $val => $title) : ?>
								<option value="<?php echo $val; ?>" <?php selected( isset( $options[$args['name']] ) && $options[$args['name']]==$val, true ); ?>><?php echo $title; ?></option>
							<?php endforeach; ?>
							</select>
					<?php echo $label; ?>
					</label>
					<?php echo $description;
			break;
			default : ?> TYPE manquant ! <?php
		}
		if( !isset( $args['fieldset'] ) || $args['fieldset']=='end' )
			echo '</fieldset>';
	}
}


/**
 * Used to display the defered module on settings form
 *
 * Since 1.1.0
 *
 */
function rocket_defered_module()
{
	$options = get_option( WP_ROCKET_SLUG );
?>
	<fieldset>
	<legend class="screen-reader-text"><span><?php _e( 'Fichiers <strong>JS</strong> en chargement différé (Deferred Loading JavaScript)', 'rocket' ); ?></span></legend>
	<div id="rktdrop">
		<?php
		if( count( $options['deferred_js_files'] ) ) {
			foreach( $options['deferred_js_files'] as $k=>$_url ) {
				$checked = isset( $options['deferred_js_wait'][$k] ) ? checked( $options['deferred_js_wait'][$k], '1', false ) : '';
				// The loop on files
			?>
			<div class="rktdrag">
				<img class="rktmove hide-if-no-js" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAIAAAD9iXMrAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAAsSAAALEgHS3X78AAAAFnRFWHRDcmVhdGlvbiBUaW1lADAxLzIxLzEwY83pkAAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNAay06AAAADqSURBVCiRjZGxasMwEIZ/ydLmh+gQQ5vNmI6FrMkL9C0CXpMOHtrQJUMHv4BXkyFD4kHgoWPAa/MCeQr7ZHWQbBRKQr/p7ufjjpOYUgr/QABIkiQMwztSnuccgNaaiPTAy/KkrwHAAfR9T0Rd1xHRLG0AzNKGPK48q1abKYBqM+08nEdEbdvOVz9jags/YUqpKIpePy+3jti9PZRlyQEEQbDPJgD22eRvIYRw7yKEkFIeP57GGVJKY8zh/XFM3Dw5sFifASzWZ+nhPMZYMFBvYwD1NhYebi/n3DaW769n/w5jjPOKorjzaZZfO46WvQF5uikAAAAASUVORK5CYII%3D" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />
				<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Attendre le chargement de ce fichier ?', 'rocket' ); ?></label>
				<img class="rktdelete hide-if-no-js" style="vertical-align:middle" src="<?php echo admin_url( '/images/no.png' ); ?>" title="<?php _e( 'Delete' ); ?>" alt="<?php _e( 'Delete' ); ?>" width="16" height="16" />
			</div>
			<?php }
		}else{
			// If no files yet, use this template inside #rktdrop
			?>
			<div class="rktdrag">
				<img class="rktmove hide-if-no-js" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAIAAAD9iXMrAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAAsSAAALEgHS3X78AAAAFnRFWHRDcmVhdGlvbiBUaW1lADAxLzIxLzEwY83pkAAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNAay06AAAADqSURBVCiRjZGxasMwEIZ/ydLmh+gQQ5vNmI6FrMkL9C0CXpMOHtrQJUMHv4BXkyFD4kHgoWPAa/MCeQr7ZHWQbBRKQr/p7ufjjpOYUgr/QABIkiQMwztSnuccgNaaiPTAy/KkrwHAAfR9T0Rd1xHRLG0AzNKGPK48q1abKYBqM+08nEdEbdvOVz9jags/YUqpKIpePy+3jti9PZRlyQEEQbDPJgD22eRvIYRw7yKEkFIeP57GGVJKY8zh/XFM3Dw5sFifASzWZ+nhPMZYMFBvYwD1NhYebi/n3DaW769n/w5jjPOKorjzaZZfO46WvQF5uikAAAAASUVORK5CYII%3D" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />
				<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Attendre le chargement de ce fichier ?', 'rocket' ); ?></label>
			</div>
		<?php } ?>
	</div>
	<?php // Clone Template ?>
	<div class="rktmodel rktdrag hide-if-js">
		<img class="rktmove hide-if-no-js" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAA0AAAANCAIAAAD9iXMrAAAAA3NCSVQICAjb4U/gAAAACXBIWXMAAAsSAAALEgHS3X78AAAAFnRFWHRDcmVhdGlvbiBUaW1lADAxLzIxLzEwY83pkAAAABx0RVh0U29mdHdhcmUAQWRvYmUgRmlyZXdvcmtzIENTNAay06AAAADqSURBVCiRjZGxasMwEIZ/ydLmh+gQQ5vNmI6FrMkL9C0CXpMOHtrQJUMHv4BXkyFD4kHgoWPAa/MCeQr7ZHWQbBRKQr/p7ufjjpOYUgr/QABIkiQMwztSnuccgNaaiPTAy/KkrwHAAfR9T0Rd1xHRLG0AzNKGPK48q1abKYBqM+08nEdEbdvOVz9jags/YUqpKIpePy+3jti9PZRlyQEEQbDPJgD22eRvIYRw7yKEkFIeP57GGVJKY8zh/XFM3Dw5sFifASzWZ+nhPMZYMFBvYwD1NhYebi/n3DaW769n/w5jjPOKorjzaZZfO46WvQF5uikAAAAASUVORK5CYII%3D" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
		<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />
		<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][]" value="1" /> <?php _e( 'Attendre le chargement de ce fichier ?', 'rocket' ); ?></label>
	</div>

	<p><a href="javascript:void(0)" id="rktclone" class="hide-if-no-js button-secondary"><?php _e( 'Ajouter une URL', 'rocket' ); ?></a></p>
	<p class="description"><?php _e( 'Vous avez la possibilité d’ajouter des fichiers Javascript qui seront chargés de manière asynchrone en même temps que le chargement de la page.', 'rocket' ); ?></p>
	<p class="hide-if-js"><?php _e( 'Videz le contenu d\'un champ pour le supprimer.', 'rocket' ); ?></p>
	<p class="description"><?php _e( '<strong>Attention :</strong> vous devez indiquez les URLs complètes des fichiers.', 'rocket' ); ?></p>
	</fieldset>
<?php
}


/**
 * Used to display buttons on settings form, tools tab
 *
 * Since 1.1.0
 *
 */

function rocket_button( $args )
{
?>
	<fieldset>
		<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary"/><?php echo esc_html( strip_tags( $args['button_label'] ) ); ?></a>
	</fieldset>
<?php
}



/**
 * The main settings page construtor using the required functions from WP
 * Since 1.0
 *
 * Add tabs, tools tab and change options severity
 * Since 1.1.0
 *
 */
function rocket_display_options()
{
	// Clé API
	add_settings_section( 'rocket_display_apikey_options', __( 'API KEY', 'rocket' ), '__return_false', 'apikey' );
		add_settings_field( 'rocket_api_key', __( 'Clé API :<br /><span class="description">(Validation de WP Rocket)</span>', 'rocket' ), 'rocket_field', 'apikey', 'rocket_display_apikey_options',
			array( 'type'=>'text', 'label_for'=>'consumer_key', 'label_screen'=>'Clé API', 'description'=>'Merci d\'entrer la clé API obtenue lors de votre achat.' )
		);
	// Basic
	add_settings_section( 'rocket_display_main_options', __( 'Options de base', 'rocket' ), '__return_false', 'basic' );
		add_settings_field( 'rocket_lazyload', __( 'Chargement différé des images :<br /><span class="description">(Lazyload)</span>', 'rocket' ), 'rocket_field', 'basic', 'rocket_display_main_options',
			array( 'type'=>'checkbox', 'label'=>'Activer le LazyLoad', 'label_for'=>'lazyload', 'label_screen'=>'Chargement différé des images', 'description'=>'Le LazyLoad (ou chargement différé des images) consiste à afficher les images d\'une page uniquement quand elles sont visibles par l\'internaute.<br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.' )
		);
		add_settings_field( 'rocket_min_js', __( 'Optimisation des fichiers : <br/> <span class="description">(Minification & Concaténation)</span>' ), 'rocket_field', 'basic', 'rocket_display_main_options',
			array(
				array( 'type'=>'checkbox', 'label'=>'HTML', 'name'=>'minify_html', 'label_screen'=>'Minification des fichiers' ),
				array( 'type'=>'checkbox', 'label'=>'CSS', 'name'=>'minify_css', 'label_screen'=>'Minification des fichiers' ),
				array( 'type'=>'checkbox', 'label'=>'JS', 'name'=>'minify_js', 'label_screen'=>'Minification des fichiers', 'description'=>'La minification supprime tout espace, retour à la ligne et commentaires présents dans les fichiers CSS et JavaScript. <br/>Ce mécanisme réduit le poids de chaque fichier et permet une lecture plus rapide par les navigateurs et les moteurs de recherche. <br/> La concaténation combine tous les fichiers CSS et JavaScript afin de n\'en faire plus qu\'un seul par type. <br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.<br /><strong style="color:#FF0000;">Attention : la concaténation des fichiers peut générer des problèmes d\'affichage, en cas d\'erreur nous vous conseillons de le désactiver</strong>.' ),
				// array( 'type'=>'checkbox', 'label'=>'Diviser les fichiers', 'name'=>'cut_concat', 'label_screen'=>'Diviser les fichier', 'description'=>'La concaténation des fichiers peut générer des problèmes d\'affichages dûs à certains paramètres de sécurité,<br />cochez alors cette case pour découper la chaîne en moins de 255 caractères,<br />si le problème persiste <strong>nous vous conseillons de désactiver ces 3 coches</strong>.' ),
			)
		);
		add_settings_field( 'rocket_mobile', __( 'Cache mobile :', 'rocket' ), 'rocket_field', 'basic', 'rocket_display_main_options',
			array( 'type'=>'checkbox', 'label'=>'Activer la mise en cache pour les appareils mobile.', 'label_for'=>'cache_mobile', 'label_screen'=>'Mobile', 'description'=>'Active le cache des pages chargées depuis un appareil dit "mobile".' )
		);
		add_settings_field( 'rocket_purge', __( 'Délai de Purge :', 'rocket' ), 'rocket_field', 'basic', 'rocket_display_main_options',
			array(
				array( 'type'=>'number', 'label_for'=>'purge_cron_interval', 'label_screen'=>'Délai de purge', 'fieldset'=>'start' ),
				array( 'type'=>'select', 'label_for'=>'purge_cron_unit', 'label_screen'=>'Unité de temps', 'fieldset'=>'end', 'description'=>'Par défaut le délai de purge est de 4h, cela signifie qu’une fois créé, les fichiers de cache se supprimeront automatiquement au bout de 4h avant d’être recréé.<br/>Cela peut être utile si vous affichez vos derniers tweets ou des flux rss dans votre sidebar, par exemple.<br/>Indiquez 0 pour une durée de vie illimitée.',
						'options' => array( 'SECOND_IN_SECONDS'=>'seconde(s)', 'MINUTE_IN_SECONDS'=>'minute(s)', 'HOUR_IN_SECONDS'=>'heure(s)', 'DAY_IN_SECONDS'=>'jour(s)' )
					)
				)
		);
	// Advanced
	add_settings_section( 'rocket_display_imp_options', __( 'Options avancées', 'rocket' ), '__return_false', 'advanced' );

		add_settings_field( 'rocket_purge_pages', __( 'Vider le cache des pages suivantes lors de la mise à jour d\'un article :', 'rocket' ), 'rocket_field', 'advanced', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_purge_pages', 'label_screen'=>'Vider le cache des pages suivantes lors de la mise à jour d\'un article', 'description'=>'Indiquez l\'URL des pages supplémentaires à purger lors de la mise à jour d\'un article (une par ligne).<br/>Il est possible d\'utiliser des expressions régulières (REGEX).<br/><strong>NB</strong> : Lorsque vous mettez à jour un article ou qu’un commentaire est posté, la page d\'accueil, les catégories et les tags associés à l\'article sont automatiquement supprimées du cache puis recréés grâce au robot WP Rocket.' )
		);
		add_settings_field( 'rocket_reject_uri', __( 'Ne jamais mettre en cache les pages suivantes :', 'rocket' ), 'rocket_field', 'advanced', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_reject_uri', 'label_screen'=>'Ne jamais mettre en cache les pages suivantes', 'description'=>'Indiquez l\'URL des pages à rejeter (une par ligne).<br/>Il est possible d\'utiliser des expressions régulières (REGEX).' )
		);
		add_settings_field( 'rocket_reject_cookies', __( 'Ne jamais mettre en cache les pages qui utilisent les cookies suivants :', 'rocket' ), 'rocket_field', 'advanced', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_reject_cookies', 'label_screen'=>'Ne jamais mettre en cache les pages qui utilisent les cookies suivants', 'description'=>'Indiquez les noms des cookies à rejeter (un par ligne)' )
		);
		add_settings_field( 'rocket_exclude_css', __( 'Fichiers <strong>CSS</strong> à exclure lors de la minification :', 'rocket' ), 'rocket_field', 'advanced', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'exclude_css', 'label_screen'=>'Fichiers CSS à exclure lors de la minification', 'description'=>'Indiquez l\'URL des fichiers <strong>CSS</strong> à rejeter (un par ligne)' )
		);
		add_settings_field( 'rocket_exclude_js', __( 'Fichiers <strong>JS</strong> à exclure lors de la minification :', 'rocket' ), 'rocket_field', 'advanced', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'exclude_js', 'label_screen'=>'Fichiers JS à exclure lors de la minification', 'description'=>'Indiquez l\'URL des fichiers <strong>JS</strong> à rejeter (un par ligne)' )
		);
		add_settings_field( 'rocket_deferred_js', __( 'Fichiers <strong>JS</strong> en chargement différé (Deferred Loading JavaScript) :', 'rocket' ), 'rocket_defered_module', 'advanced', 'rocket_display_imp_options' );
	// Tools
	add_settings_section( 'rocket_display_tools', __( 'Outils', 'rocket' ), '__return_false', 'tools' );

		add_settings_field( 'rocket_purge_all', __( 'Vider le cache', 'rocket' ), 'rocket_button', 'tools', 'rocket_display_tools',
			array( 'button_label'=>__( 'Vider le cache', 'rocket' ), 'url'=>wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ), 'description'=>'Permet de purger le cache du site complet.' )
		);
		 add_settings_field( 'rocket_preload', __( 'Précharger le cache', 'rocket' ), 'rocket_button', 'tools', 'rocket_display_tools',
            array( 'button_label'=>__( 'Précharger le cache', 'rocket' ), 'url'=>wp_nonce_url( admin_url( 'admin-post.php?action=preload' ), 'preload' ), 'description'=>'Permet de demander le passage du robot pour précharger le cache (homepage + liens internes de cette page).' )
        );
?>
	<div class="wrap">
	<div id="icon-rocket" class="icon32" style="background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RTEwQzc4NjhDRjc4MTFFMjg4QUZFMzFGRTgwOTgwNjgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RTEwQzc4NjlDRjc4MTFFMjg4QUZFMzFGRTgwOTgwNjgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpFMTBDNzg2NkNGNzgxMUUyODhBRkUzMUZFODA5ODA2OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpFMTBDNzg2N0NGNzgxMUUyODhBRkUzMUZFODA5ODA2OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PpIJhBsAAAWPSURBVHjatJd/TJR1HMef435xdBxcEoesX6DR0OagoaJcLeiHjMWaZpSttbnox/yxmbXKpaYu1yJra47+qc2lqautlZWTy9GakBEMxH7ISDApWEpBnAccd9xxvT/4fth3t+c6zPndXoPnue/zfN7fz+fz/Xy+j6m+vl67ipEK3KAUFIPbwQ0gEzg4JwRGwQi4CAbAb+A8aLf8T8PyXBF4AtwHbgR2YAMpSZ4VQWEQAe9fqQATV/cCqOXqbVf4DjuRcb9lFgZzwAJwM/CAarrbMQtjMRAEXeAn0Erx8/j79YkE3AQeAHeBO8EcxltW60xiVGLbDc7QsPA7GAN+sEuZ64wXIMn0DCgDWaIwyep0IxdpuIfXYizA5Isqz9zNxejDpgswgyqwGSxhkvzNzL31PxKrE7wDzoIJMJnEOxUM60yILUqsxdhuME5XSaI8DjYmSDR5phzcAvrAAeBLImCx0XbS6KYfuPLrwBrGf6kyx2jkEi84MovsL0kkIMZVPsXttSiR4Vgsdnn5phlPDoMNoDGJgDtAdty9qBrbTO7ro2A9E2tmTE1NaTabTcvIyBBiFotFi0ajf1H0YQrRzGZzIgFeg3t+dZXygg9ZKmUH1KmGXS5Xv9/v39/f39+D1dshYi7utY6NjR2dWU40qlmt1um/BmOpwb2zqoBxImMTyBB3i/HU1NSPfT6fZLtjYGCgEPfP1dTUfAARkcnJy4k/MTExEx5lSB41cRcZCWgyirP48El5mbgzPT39s4aGhqdbWlqezc7OlqLUjRU2dnR0ROx2uxYKhaYfKiwsjH/PNrCShczJ3RI/PjcSkMdKqKWlpQ02NzdvbG9vr/V4PGthcAs8MgFxRd3d3fMhxAeRUwYC1oGtTOxFbDzxyfGzlGcjAVL3p2MZDAbfbWtri2VmZr7udDofRD64YfR56QUQInXjGNgiERBvIBw2iNvAcqvXDpl/zsDOG5JiRgKmS6W8EPHeh/jXOhyOQzB+mn3cxXke9v8fkZT74CUtPz9/LjxVF4lE1NWuYmFTxynwqZagxJqUfe6Hi0tY5aoV4+pYLXPhqXuHhobMEqK43116SJWxk+cCQwFDqhhkdwAGJIF+NezXJlODFBl47DWE6jzbb7IW3axfGAmQvq3BjRpcX1xQUHBwfHz85ZSUlO9x+5O4uY3hcHgvcmUPfj+Zm5ubgV0xJ4mAS/rqtQTlVuLcipUvcbvdW71eb2VXV9dOVL7dyIdXxBNItHlYuRSswyhEa5GYC0tLSytxvw4CTEkE9LFNJxQgLtqGl/mQ1Svw/yMlJSVVnZ2dR3Cdgpw4CU4gKS3wUiWErCoqKnqsvLx82fDw8CaDYqSxTVv5/2mjZqTugNvk+IUXYzGhCGrB/rKysmq8eE1vb+86GK2CkAA8koa4h5H5z1VUVFhHR0cPYb5VaVL6+A4MsijJ+DqRAMnUl5jtknT1iOujiH8WjB+HF7bD2LGRkRFtcHDQmZWVFUSIQki85ciBHZjnxnxNae8RtuAdbPErGfvjRgLEPW+Lu3n9Dffqw3jpKeRDMdy+KycnR8M+/zYvL68PhcqDe8th2AUBGo2HeP6TAlVDj0qbXs33fhHfZS3KUUk3Lmc5+Vp5EeyVFg23LkDMOwOBgJz/F8OYF9lvgmfC+G0YXOKLm3gwaeY3w0fMKb1Ov5XoQLJeubeHdfoA3biZp6WH2N0WQoy0azMMSzZf4OFUzof/KGcLif2rvBYBso3bjARIp7pHORPU8VtgPo1r3P9S+78ks/lkk/P/n6ysrVyY4SeWfGSk8/oX1u7trPMyeunKKxkXiD7eU4tPvIBlyrWIOajUgx7G0n8VH7CxRMZ1ASuUawcni7u/Am/yzH/NhoUfFGfosj/kkxmcYPMJatd4/CvAAKFM+DWaJ2g7AAAAAElFTkSuQmCC) 0 0 no-repeat;"><br/></div>
	<h2>WP Rocket <small>v<?php echo WP_ROCKET_VERSION; ?></small></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'wp_rocket' ); ?>
		<?php submit_button(); ?>
		<div id="tabs">
			<ul class="hide-if-no-js">
				<?php if( rocket_valid_key() ) : ?>
				<li><a href="#tab_basic"><?php _e( 'Options de base', 'rocket' ); ?></a></li>
				<li><a href="#tab_advanced"><?php _e( 'Options avancées', 'rocket' ); ?></a></li>
				<li><a href="#tab_tools"><?php _e( 'Outils', 'rocket' ); ?></a></li>
				<?php endif; ?>
				<li><a href="#tab_apikey"><?php _e( 'API KEY', 'rocket' ); ?></a></li>
			</ul>
  			<div id="tab_apikey"><?php do_settings_sections( 'apikey' ); ?></div>
			<?php if( rocket_valid_key() ) : ?>
			<div id="tab_basic"><?php do_settings_sections( 'basic' ); ?></div>
			<div id="tab_advanced"><?php do_settings_sections( 'advanced' ); ?></div>
			<div id="tab_tools"><?php do_settings_sections( 'tools' ); ?></div>
			<?php endif; ?>
		</div>
		<?php submit_button(); ?>
	</form>
<?php
}



/**
 * When the options 'comment_whitelist' & 'comment_moderation' are modified, we flush the htaccess
 *
 * Since 1.0
 *
 */

add_action( 'updated_option', 'rocket_flush_for_comment', 10, 3 );
function rocket_flush_for_comment( $option, $oldvalue, $_newvalue )
{
	if( ( $option=='comment_whitelist' || $option=='comment_moderation' ) && $oldvalue!=$_newvalue )
		flush_rocket_htaccess();
}



/**
 * Tell to WordPress to be confident with uor setting, we are clean!
 *
 * Since 1.0
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
 * Since 1.0
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
 * Since 1.0
 *
 */

function rocket_sanitize_js( $file )
{
	$file = preg_replace( '#\?.*$#', '', $file );
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext=='js' ? $file : false;
}



/**
 * Used to clean and sanitize the settings fields
 *
 * Since 1.0
 *
 */

function rocket_settings_callback( $inputs )
{
	$options = get_option( WP_ROCKET_SLUG );
	// Clean inputs
	$inputs['cache_purge_pages'] = 		isset( $inputs['cache_purge_pages'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					explode( "\n", trim( $inputs['cache_purge_pages'] ) ) ) ) ) 	)	: array();
	$inputs['cache_reject_uri'] = 		isset( $inputs['cache_reject_uri'] ) ? 		array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					explode( "\n", trim( $inputs['cache_reject_uri'] ) ) ) ) ) 		)	: array();
	$inputs['cache_reject_cookies'] = 	isset( $inputs['cache_reject_cookies'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'sanitize_key', 				explode( "\n", trim( $inputs['cache_reject_cookies'] ) ) ) ) ) 	)	: array();
	$inputs['exclude_css'] = 			isset( $inputs['exclude_css'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_css', 		array_map( 'rocket_clean_exclude_file',	explode( "\n", trim( $inputs['exclude_css'] ) ) ) ) ) 			)	: array();
	$inputs['exclude_js'] = 			isset( $inputs['exclude_js'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_js', 		array_map( 'rocket_clean_exclude_file',	explode( "\n", trim( $inputs['exclude_js']) ) ) ) ) 			)	: array();
	$inputs['deferred_js_files'] = 		isset( $inputs['deferred_js_files'] ) ? 		array_filter( array_map( 'rocket_sanitize_js', 																	 array_unique(	 $inputs['deferred_js_files'] ) ) ) 				: array();
	if( !$inputs['deferred_js_files'] ){
		$inputs['deferred_js_wait'] = array();
	}else{
		for( $i=0; $i<=max(array_keys($inputs['deferred_js_files'])); $i++) {
			if( !isset( $inputs['deferred_js_files'][$i] ) )
				unset( $inputs['deferred_js_wait'][$i] );
			else $inputs['deferred_js_wait'][$i] = isset( $inputs['deferred_js_wait'][$i] ) ? '1' : '0';
		}
		$inputs['deferred_js_files'] = array_values( $inputs['deferred_js_files'] );
		ksort( $inputs['deferred_js_wait'] );
		$inputs['deferred_js_wait'] = array_values( $inputs['deferred_js_wait'] );
	}

	$inputs['purge_cron_interval'] = 	isset( $inputs['purge_cron_interval'] ) ? 	(int)$inputs['purge_cron_interval'] : $options['purge_cron_interval'];
	$inputs['purge_cron_unit'] = 		isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : $options['purge_cron_unit'];
	if( $inputs['consumer_key']==hash( 'crc32', rocket_get_domain( home_url() ) ) ){
		$response = wp_remote_get( WP_ROCKET_WEB_VALID, array( 'timeout'=>30 ) );
		if( !is_a($response, 'WP_Error') && strlen( $response['body'] )==32 )
			$inputs['secret_key'] = $response['body'];
	}else{
			unset( $inputs['secret_key'] );
	}
	rocket_renew_box( 'rocket_warning_logged_users' );
	return $inputs;
}



/**
 * When our settings are saved: purge, cron, flush, preload!
 *
 * Since 1.0
 *
 */

add_action( 'update_option_wp_rocket_settings', 'rocket_after_save_options' );
function rocket_after_save_options()
{

	// Purge all cache files when user save options
	rocket_clean_domain();

	// Update .htaccess file rules
	flush_rocket_htaccess( !rocket_valid_key() );

}

/**
 * When purge settings are saved we change the scheduled purge
 *
 * Since 1.0
 *
 */

add_filter( 'pre_update_option_'.WP_ROCKET_SLUG, 'rocket_pre_main_option', 10, 2 );
function rocket_pre_main_option( $newvalue, $oldvalue )
{
  if( ($newvalue['purge_cron_interval']!=$oldvalue['purge_cron_interval']) || ($newvalue['purge_cron_unit']!=$oldvalue['purge_cron_unit']) )
  {
  	// Clear WP Rocket cron
	if (wp_next_scheduled( 'rocket_purge_time_event' ) )
		wp_clear_scheduled_hook( 'rocket_purge_time_event' );
  }
  return $newvalue;
}

/**
 * We keep the last opened tab, open the next time
 *
 * Since 1.1.10
 *
 */

add_action( 'admin_footer-settings_page_wprocket', 'rocket_add_script_in_options' );
function rocket_add_script_in_options()
{ ?>
<script>
	function setCookie(c_name,value,exdays)
	{
		var exdate=new Date();
		exdate.setDate(exdate.getDate() + exdays);
		var c_value=escape(value) + ((exdays==null) ? "" : "; expires="+exdate.toUTCString());
		document.cookie=c_name + "=" + c_value;
	}

	function getCookie(c_name) {
	    var c_value = document.cookie;
	    var c_start = c_value.indexOf(" " + c_name + "=");
	    if (c_start == -1) {
	        c_start = c_value.indexOf(c_name + "=");
	    }
	    if (c_start == -1) {
	        c_value = null;
	    } else {
	        c_start = c_value.indexOf("=", c_start) + 1;
	        var c_end = c_value.indexOf(";", c_start);
	        if (c_end == -1) {
	            c_end = c_value.length;
	        }
	        c_value = unescape(c_value.substring(c_start, c_end));
	    }
	    return c_value;
	}

	jQuery( document ).ready( function($){
		var tab = '';
		if( tab = getCookie( 'rocket_tab' )  ) {
			$('#tabs a[href="'+tab+'"]').click();
			// window.location.hash = tab;
		}
		$('#tabs li.ui-state-default a').on( 'click', function(){
			tab = $(this).attr( 'href' );
			setCookie( 'rocket_tab', tab, 365 );
			// window.location.hash = tab;
		} );
	} );
</script>
<?php }