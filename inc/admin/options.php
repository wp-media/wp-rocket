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
		$label = isset( $args['label'] ) ? $args['label'] : '' ;
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
 * The main settings page construtor using the required functions from WP
 *
 * Since 1.0
 *
 */
function rocket_display_options()
{
	add_settings_section( 'rocket_display_apikey_options', __( 'API KEY', 'rocket' ), '__return_false', 'apikey' );
		add_settings_field( 'rocket_api_key', __( 'Clé API :<br /><span class="description">(Validation de WP Rocket)</span>', 'rocket' ), 'rocket_field', 'apikey', 'rocket_display_apikey_options',
			array( 'type'=>'text', 'label_for'=>'consumer_key', 'label_screen'=>'Clé API', 'description'=>'Merci d\'entrer la clé API obtenue lors de votre achat.' )
		);
	add_settings_section( 'rocket_display_main_options', __( 'Options générales', 'rocket' ), '__return_false', 'general' );
		add_settings_field( 'rocket_lazyload', __( 'Chargement différé des images :<br /><span class="description">(Lazyload)</span>', 'rocket' ), 'rocket_field', 'general', 'rocket_display_main_options',
			array( 'type'=>'checkbox', 'label'=>'Activer le LazyLoad', 'label_for'=>'lazyload', 'label_screen'=>'Chargement différé des images', 'description'=>'Le LazyLoad (ou chargement différé des images) consiste à afficher les images d\'une page uniquement quand elles sont visibles par l\'internaute.<br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.' )
		);
		add_settings_field( 'rocket_min_js', __( 'Optimisation des fichiers : <br/> <span class="description">(Minification & Concaténation)</span>' ), 'rocket_field', 'general', 'rocket_display_main_options',
			array(
				array( 'type'=>'checkbox', 'label'=>'CSS', 'name'=>'minify_css', 'label_screen'=>'Minification des fichiers' ),
				array( 'type'=>'checkbox', 'label'=>'JS', 'name'=>'minify_js', 'label_screen'=>'Minification des fichiers', 'description'=>'La minification supprime tout espace, retour à la ligne et commentaires présents dans les fichiers CSS et JavaScript. <br/>Ce mécanisme réduit le poids de chaque fichier et permet une lecture plus rapide par les navigateurs et les moteurs de recherche. <br/> La concaténation combine tous les fichiers CSS et JavaScript afin de n\'en faire plus qu\'un seul par type. <br/>Ce mécanisme réduit le nombre de requêtes HTTP et améliore le temps de chargement des pages.</p> <br/> Attention : la concaténation des fichiers peut générer des problèmes d\'affichages, <strong>en cas d\'erreur nous vous conseillons de le désactiver</strong>.' ),
			)
		);
		add_settings_field( 'rocket_mobile', __( 'Cache mobile :', 'rocket' ), 'rocket_field', 'general', 'rocket_display_main_options',
			array( 'type'=>'checkbox', 'label'=>'Activer la mise en cache pour les appareils mobile.', 'label_for'=>'cache_mobile', 'label_screen'=>'Mobile', 'description'=>'Active le cache des pages chargées depuis un appareil dit "mobile".' )
		);
	add_settings_section( 'rocket_display_imp_options', __( 'Options avancées', 'rocket' ), '__return_false', 'improved' );
		add_settings_field( 'rocket_purge', __( 'Délai de Purge :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array(
				array( 'type'=>'number', 'label_for'=>'purge_cron_interval', 'label_screen'=>'Délai de purge', 'fieldset'=>'start' ),
				array( 'type'=>'select', 'label_for'=>'purge_cron_unit', 'label_screen'=>'Unité de temps', 'fieldset'=>'end', 'description'=>'Vous pouvez spécifier une durée de vie pour les fichiers de cache.<br/>Indiquez 0 pour une durée de vie illimitée.',
						'options' => array( 'SECOND_IN_SECONDS'=>'seconde(s)', 'MINUTE_IN_SECONDS'=>'minute(s)', 'HOUR_IN_SECONDS'=>'heure(s)', 'DAY_IN_SECONDS'=>'jour(s)' )
					)
				)
		);
		add_settings_field( 'rocket_purge_pages', __( 'Vider le cache des pages suivantes lors de la mise à jour d\'un article :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_purge_pages', 'label_screen'=>'Vider le cache des pages suivantes lors de la mise à jour d\'un article', 'description'=>'Indiquez l\'URL des pages supplémentaires à purger lors de la mise à jour d\'un article (une par ligne).<br/>Il est possible d\'utiliser des expressions régulières (REGEX).' )
		);
		add_settings_field( 'rocket_reject_uri', __( 'Ne jamais mettre en cache les pages suivantes :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_reject_uri', 'label_screen'=>'Ne jamais mettre en cache les pages suivantes', 'description'=>'Indiquez l\'URL des pages à rejeter (une par ligne).<br/>Il est possible d\'utiliser des expressions régulières (REGEX).' )
		);
		add_settings_field( 'rocket_reject_cookies', __( 'Ne jamais mettre en cache les pages qui utilisent les cookies suivants :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'cache_reject_cookies', 'label_screen'=>'Ne jamais mettre en cache les pages qui utilisent les cookies suivants', 'description'=>'Indiquez les noms des cookies à rejeter (un par ligne)' )
		);
		add_settings_field( 'rocket_exclude_css', __( 'Fichiers <strong>CSS</strong> à exclure lors de la minification :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'exclude_css', 'label_screen'=>'Fichiers CSS à exclure lors de la minification', 'description'=>'Indiquez l\'URL des fichiers <strong>CSS</strong> à rejeter (un par ligne)' )
		);
		add_settings_field( 'rocket_exclude_js', __( 'Fichiers <strong>JS</strong> à exclure lors de la minification :', 'rocket' ), 'rocket_field', 'improved', 'rocket_display_imp_options',
			array( 'type'=>'textarea', 'label_for'=>'exclude_js', 'label_screen'=>'Fichiers JS à exclure lors de la minification', 'description'=>'Indiquez l\'URL des fichiers <strong>JS</strong> à rejeter (un par ligne)' )
		);
?>
	<div class="wrap">
	<div id="icon-rocket" class="icon32" style="background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAACAAAAAgCAYAAABzenr0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyJpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuMy1jMDExIDY2LjE0NTY2MSwgMjAxMi8wMi8wNi0xNDo1NjoyNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENTNiAoV2luZG93cykiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6RTEwQzc4NjhDRjc4MTFFMjg4QUZFMzFGRTgwOTgwNjgiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6RTEwQzc4NjlDRjc4MTFFMjg4QUZFMzFGRTgwOTgwNjgiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDpFMTBDNzg2NkNGNzgxMUUyODhBRkUzMUZFODA5ODA2OCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDpFMTBDNzg2N0NGNzgxMUUyODhBRkUzMUZFODA5ODA2OCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PpIJhBsAAAWPSURBVHjatJd/TJR1HMef435xdBxcEoesX6DR0OagoaJcLeiHjMWaZpSttbnox/yxmbXKpaYu1yJra47+qc2lqautlZWTy9GakBEMxH7ISDApWEpBnAccd9xxvT/4fth3t+c6zPndXoPnue/zfN7fz+fz/Xy+j6m+vl67ipEK3KAUFIPbwQ0gEzg4JwRGwQi4CAbAb+A8aLf8T8PyXBF4AtwHbgR2YAMpSZ4VQWEQAe9fqQATV/cCqOXqbVf4DjuRcb9lFgZzwAJwM/CAarrbMQtjMRAEXeAn0Erx8/j79YkE3AQeAHeBO8EcxltW60xiVGLbDc7QsPA7GAN+sEuZ64wXIMn0DCgDWaIwyep0IxdpuIfXYizA5Isqz9zNxejDpgswgyqwGSxhkvzNzL31PxKrE7wDzoIJMJnEOxUM60yILUqsxdhuME5XSaI8DjYmSDR5phzcAvrAAeBLImCx0XbS6KYfuPLrwBrGf6kyx2jkEi84MovsL0kkIMZVPsXttSiR4Vgsdnn5phlPDoMNoDGJgDtAdty9qBrbTO7ro2A9E2tmTE1NaTabTcvIyBBiFotFi0ajf1H0YQrRzGZzIgFeg3t+dZXygg9ZKmUH1KmGXS5Xv9/v39/f39+D1dshYi7utY6NjR2dWU40qlmt1um/BmOpwb2zqoBxImMTyBB3i/HU1NSPfT6fZLtjYGCgEPfP1dTUfAARkcnJy4k/MTExEx5lSB41cRcZCWgyirP48El5mbgzPT39s4aGhqdbWlqezc7OlqLUjRU2dnR0ROx2uxYKhaYfKiwsjH/PNrCShczJ3RI/PjcSkMdKqKWlpQ02NzdvbG9vr/V4PGthcAs8MgFxRd3d3fMhxAeRUwYC1oGtTOxFbDzxyfGzlGcjAVL3p2MZDAbfbWtri2VmZr7udDofRD64YfR56QUQInXjGNgiERBvIBw2iNvAcqvXDpl/zsDOG5JiRgKmS6W8EPHeh/jXOhyOQzB+mn3cxXke9v8fkZT74CUtPz9/LjxVF4lE1NWuYmFTxynwqZagxJqUfe6Hi0tY5aoV4+pYLXPhqXuHhobMEqK43116SJWxk+cCQwFDqhhkdwAGJIF+NezXJlODFBl47DWE6jzbb7IW3axfGAmQvq3BjRpcX1xQUHBwfHz85ZSUlO9x+5O4uY3hcHgvcmUPfj+Zm5ubgV0xJ4mAS/rqtQTlVuLcipUvcbvdW71eb2VXV9dOVL7dyIdXxBNItHlYuRSswyhEa5GYC0tLSytxvw4CTEkE9LFNJxQgLtqGl/mQ1Svw/yMlJSVVnZ2dR3Cdgpw4CU4gKS3wUiWErCoqKnqsvLx82fDw8CaDYqSxTVv5/2mjZqTugNvk+IUXYzGhCGrB/rKysmq8eE1vb+86GK2CkAA8koa4h5H5z1VUVFhHR0cPYb5VaVL6+A4MsijJ+DqRAMnUl5jtknT1iOujiH8WjB+HF7bD2LGRkRFtcHDQmZWVFUSIQki85ciBHZjnxnxNae8RtuAdbPErGfvjRgLEPW+Lu3n9Dffqw3jpKeRDMdy+KycnR8M+/zYvL68PhcqDe8th2AUBGo2HeP6TAlVDj0qbXs33fhHfZS3KUUk3Lmc5+Vp5EeyVFg23LkDMOwOBgJz/F8OYF9lvgmfC+G0YXOKLm3gwaeY3w0fMKb1Ov5XoQLJeubeHdfoA3biZp6WH2N0WQoy0azMMSzZf4OFUzof/KGcLif2rvBYBso3bjARIp7pHORPU8VtgPo1r3P9S+78ks/lkk/P/n6ysrVyY4SeWfGSk8/oX1u7trPMyeunKKxkXiD7eU4tPvIBlyrWIOajUgx7G0n8VH7CxRMZ1ASuUawcni7u/Am/yzH/NhoUfFGfosj/kkxmcYPMJatd4/CvAAKFM+DWaJ2g7AAAAAElFTkSuQmCC) 0 0 no-repeat;"><br/></div>
	<h2>WP Rocket <small>v<?php echo WP_ROCKET_VERSION; ?></small></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'wp_rocket' ); ?>
		<?php submit_button(); ?>
		<div ><?php do_settings_sections( 'apikey' ); ?></div>
		<?php if( rocket_valid_key() ) : ?>
		<div ><?php do_settings_sections( 'general' ); ?></div>
		<div ><?php do_settings_sections( 'improved' ); ?></div>
		<?php endif; ?>
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
	$inputs['cache_purge_pages'] = 		isset( $inputs['cache_purge_pages'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					explode( "\n", trim( $inputs['cache_purge_pages'] ) ) ) ) ) 		) : '';
	$inputs['cache_reject_uri'] = 		isset( $inputs['cache_reject_uri'] ) ? 		array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					explode( "\n", trim( $inputs['cache_reject_uri'] ) ) ) ) ) 		) : '';
	$inputs['cache_reject_cookies'] = 	isset( $inputs['cache_reject_cookies'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'sanitize_key', 				explode( "\n", trim( $inputs['cache_reject_cookies'] ) ) ) ) ) 	) : '';
	$inputs['exclude_css'] = 			isset( $inputs['exclude_css'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_css', 		array_map( 'rocket_clean_exclude_file',	explode( "\n", trim( $inputs['exclude_css'] ) ) ) ) ) 			) : '';
	$inputs['exclude_js'] = 			isset( $inputs['exclude_js'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_js', 		array_map( 'rocket_clean_exclude_file',	explode( "\n", trim( $inputs['exclude_js']) ) ) ) ) 			) : '';
	$inputs['purge_cron_interval'] = 	isset( $inputs['purge_cron_interval'] ) ? 	(int)$inputs['purge_cron_interval'] : 0;
	$inputs['purge_cron_unit'] = 		isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : '';
	if( $inputs['consumer_key']==hash( 'crc32', rocket_get_domain( home_url() ).chr(98) ) ){
		$inputs['secret_key'] = @file_get_contents( WP_ROCKET_WEB_MAIN.WP_ROCKET_WEB_VALID . '?k='.sanitize_key( $inputs['consumer_key'] ).'&u='.urlencode( rocket_get_domain( home_url() ) ).'&v='.WP_ROCKET_VERSION );
	} else {
		unset( $inputs['secret_key'] );
	}
	$inputs = wp_parse_args( array_filter( $inputs ), $options );

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

	// Clear cron
	wp_clear_scheduled_hook( 'rocket_purge_time_event' );

	// Update .htaccess file rules
	flush_rocket_htaccess( !rocket_valid_key() );

	// Run WP Rocket Bot for preload cache files
	run_rocket_bot( 'cache-preload', home_url() );
}