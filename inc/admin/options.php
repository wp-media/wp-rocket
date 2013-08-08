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
				$value = esc_attr( get_rocket_option( $args['name'], '' ) );
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
				$t_temp = get_rocket_option( $args['name'], '' );
				$value = !empty( $t_temp ) ? esc_textarea( implode( "\n" , $t_temp ) ) : '';
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="50" rows="5"><?php echo $value; ?></textarea>
					</label>
					<?php echo $description; ?>
				<?php
			break;
			case 'checkbox' :
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input type="checkbox" id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1" <?php checked( get_rocket_option( $args['name'], 0 ), 1 ); ?>/> <?php echo $args['label']; ?>
					</label>
					<?php echo $description; ?>
				<?php
			break;
			case 'select' :
				?>
					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label>	<select id="<?php echo $args['name']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]">
							<?php foreach( $args['options'] as $val => $title) : ?>
								<option value="<?php echo $val; ?>" <?php selected( get_rocket_option( $args['name'] ), $val ); ?>><?php echo $title; ?></option>
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
 * @since 1.1.0
 *
 */

function rocket_defered_module()
{
?>
	<fieldset>
	<legend class="screen-reader-text"><span><?php _e( 'Fichiers <strong>JS</strong> en chargement différé (Deferred Loading JavaScript)', 'rocket' ); ?></span></legend>
	<div id="rktdrop">
		<?php
		$deferred_js_files = get_rocket_option( 'deferred_js_files' );
		$deferred_js_wait = get_rocket_option( 'deferred_js_wait' );
		if( $deferred_js_files ) {
			foreach( $deferred_js_files as $k=>$_url ) {
				$checked = isset( $deferred_js_wait[$k] ) ? checked( $deferred_js_wait[$k], '1', false ) : '';
				// The loop on files
			?>
			<div class="rktdrag">
				<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />
				<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Attendre le chargement de ce fichier ?', 'rocket' ); ?></label>
				<img class="rktdelete hide-if-no-js" style="vertical-align:middle" src="<?php echo admin_url( '/images/no.png' ); ?>" title="<?php _e( 'Delete' ); ?>" alt="<?php _e( 'Delete' ); ?>" width="16" height="16" />
			</div>
			<?php }
		}else{
			// If no files yet, use this template inside #rktdrop
			?>
			<div class="rktdrag">
				<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />
				<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Attendre le chargement de ce fichier ?', 'rocket' ); ?></label>
			</div>
		<?php } ?>
	</div>
	<?php // Clone Template ?>
	<div class="rktmodel rktdrag hide-if-js">
		<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
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
 * @since 1.1.0
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
	<div id="icon-rocket" class="icon32"></div>
	<h2>WP Rocket <small>v<?php echo WP_ROCKET_VERSION; ?></small></h2>

	<form action="options.php" method="post">
		<?php settings_fields( 'wp_rocket' ); ?>
		<?php submit_button(); ?>
		<h2 class="nav-tab-wrapper hide-if-no-js">
			<?php if( rocket_valid_key() ) : ?>
				<a href="#tab_basic" class="nav-tab"><?php _e( 'Options de base', 'rocket' ); ?></a>
				<a href="#tab_advanced" class="nav-tab"><?php _e( 'Options avancées', 'rocket' ); ?></a>
				<a href="#tab_tools" class="nav-tab"><?php _e( 'Outils', 'rocket' ); ?></a>
				<a href="#tab_tutos" class="nav-tab"><?php _e( 'Tutoriels', 'rocket' ); ?></a>
				<a href="#tab_faq" class="nav-tab"><?php _e( 'F.A.Q.', 'rocket' ); ?></a>
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
				<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'tools' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_tutos">
					<h2>Préchargement des fichiers de cache</h2>
					<p>Cette vidéo donne quelques explications sur les 2 robots crawler de WP Rocket. Ils permettent de générer plusieurs vingtaines de fichiers de cache en quelques secondes.</p>
					<p><a href="http://www.youtube.com/embed/9jDcg2f-9yM" class="button-primary fancybox">Voir la vidéo</a></p>

					<h2>Minification des fichiers CSS et JavaScript</h2>
					<p>Cette vidéo donne quelques explications sur l’utilisation avancée du processus de minification et concaténation des fichiers CSS et JavaScript.</p>
					<p><a href="http://www.youtube.com/embed/iziXSvZgxLk" class="button-primary fancybox">Voir la vidéo</a></p>
				</div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq">
					<h2>Que fait exactement WP Rocket ? </h2>
					<p>WP Rocket est un plugin de cache complet qui embarque de nombreuses fonctionnalités :</p>
					<ul>
						<li>Mise en cache de l'ensemble des pages pour un affichage rapide</li>
						<li>Préchargement des fichiers de cache à l'aide de 2 robots en Python</li>
						<li>Réduction du nombres de requêtes HTTP pour réduire le temps de chargement</li>
						<li>Diminution de la bande passante grâce à la compression GZIP</li>
						<li>Gestion des headers (expire, etags, etc...)</li>
						<li>Minification et concaténations des JS et CSS</li>
						<li>Chargement différé des images (LazyLoad)</li>
						<li>Chargement différé des fichiers JavaScript</li>
						<li>Optimisation des images</li>
					</ul>

					<h2>J'ai activé aucune des options de base, est-ce que WP Rocket fonctionne ?</h2>
					<p>Oui.</p>
					<p>Les options de base sont des optimisations complémentaires que l’on peut qualifié de bonus. Ces options ne sont pas indispensables pour améliorer le temps de chargement de votre site Internet.</p>
					<p>Quelque soit votre configuration de WP Rocket, les fonctionnalités suivantes seront toujours actives :</p>
					<ul>
						<li>Mise en cache de l'ensemble des pages pour affichage rapide</li>
						<li>Diminution de la bande passante grâce à la compression GZIP</li>
						<li>Gestion des headers (expire, etags, etc)</li>
						<li>Optimisation des images</li>
					</ul>

					<h2>Que dois-je faire en cas de problème lié à WP Rocket que je n’arrive pas à résoudre ?</h2>
					<p>Si aucune des réponses de la F.A.Q. présente ci-dessous apporte une réponse à votre problématique, vous pouvez nous faire part de votre problème sur notre <a href="http://support.wp-rocket.me" target="_blank">support</a>. Nous vous répondrons dans les plus brefs délais.</p>

					<h2>Ma licence est expirée, que dois-je faire ?</h2>
					<p>Pas de panique, WP Rocket continuera de fonctionner sans problème. Vous recevrez un mail vous indiquant que votre licence va bientôt arriver à expiration. Vous trouverez un lien de renouvellement qui sera actif même après l’expiration.</p>

					<h2>Je souhaite modifier l'URL de mon site associé à ma licence, que dois-je faire ?</h2>
					<p>Vous devez nous contacter par mail (<a href="mailto:contact@wp-rocket.me">contact@wp-rocket.me</a>) en nous indiquant la raison de votre modification. La modification sera réalisée par l’équipe de WP Rocket.</p>

					<h2>Quels outils dois-je utilisé pour mesurer les performances de mon site ?</h2>
					<p>Vous pouvez mesurer les performances de votre site Internet à l’aide des outils suivants : </p>
					<ul>
						<li><a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a></li>
						<li><a href="http://gtmetrix.com/" target="_blank">GT Metrix</a></li>
						<li><a href="http://www.webpagetest.org/" target="_blank">Webpagetest</a></li>
					</ul>

					<p>Ces outils donnent 2 indications :</p>
					<ul>
						<li>une note globale des bonnes pratiques à appliquer</li>
						<li>un temps de chargement</li>
					</ul>

					<p>Ces données sont indicatives et ne reflètent pas forcément  la vitesse d’affichage réelle de votre site Internet.</p>

					<p>Pour réaliser des tests de temps de chargement plus proche de la réalité,, nous conseillons d’utiliser <a href="http://tools.pingdom.com/fpt/" target="_blank">Pingdom Tools</a> avec l’option <code>Amsterdam</code> comme serveur.</p>

					<h2>WP Rocket fonctionne-t-il avec les permaliens par défaut ?</h2>
					<p>Non.</p>

					<p>Il est nécessaire d'avoir des permaliens personnalisés du type <code>http://example.com/mon-article/</code> plutôt que <code>http://example.com/?p=1234</code>.</p>

					<h2>Avec quels serveurs Web WP Rocket est-il compatible ?</h2>
					<p>WP Rocket est compatible avec les serveurs Web <strong>Apache</strong>. Pour le moment, WP Rocket n’est donc pas compatible avec les serveurs Web NGINX et Litepseed.</p>

					<h2>Le rapport PageSpeed ou Yslow m’indique que le contenu n’est pas gzipé et/ou n’a pas d’expiration, que dois-je faire ?</h2>

					<p>WP Rocket ajoute automatiquement les bonnes règles d’expirations et de gzip des fichiers statiques. Si elles ne sont pas appliquées, il est possible qu’un plugin rentre en conflit (exemple: <a href="http://wordpress.org/plugins/wp-retina-2x/" target="_blank">WP Retina 2x</a>). Essayez de désactiver temporairement tous les plugins, excepté WP Rocket, et de refaire le test.</p>

					<p>Si cela n’est pas concluant, cela signifie que le <code>mod_expire</code> et/ou <code>mod_deflate</code> n’est pas activé sur votre serveur.</p>

					<h2>WP Rocket est-il compatible avec les autres plugins de cache, tels que WP Super Cache ou W3 Total Cache ?</h2>
					<p>Non.</p>

					<p>Il est impératif de <strong>supprimer tous les autres plugins d'optimisation</strong> (cache, minification, LazyLoad) avant l’activation de WP Rocket.</p>

					<h2>WP Rocket est-il compatible avec WP Touch ?</h2>
					<p>Oui.</p>
					<p>Par contre, dans les options de base, vous devez décocher la case <code>Activer la mise en cache pour les appareils mobile</code>.</p>

					<h2>WP Rocket est-il compatible avec WooCommerce ?</h2>
					<p>Oui.</p>

					<p>Cependant, il faut exclure les pages panier et commande de la mise en cache. Cela se fait à partir de l’option avancée <code>Ne jamais mettre en cache les pages suivantes</code> et en ajoutant les valeurs suivantes :</p>
					<p><code>/panier/<br/>
					/commande/*
					</code></p>

					<h2>WP Rocket est-il compatible avec WPML ?</h2>
					<p>Oui.</p>
					<p>Vous avez même la possibilité de vider/précharger la cache d'une langue précise ou de toutes les langues en même temps.</p>

					<h2>En quoi consiste la minification et concaténation des fichiers ?</h2>
					<p>La minification consiste à supprimer tous les éléments superflus d’une fichier HTML, CSS ou JavaScript : espaces, commentaires, etc... Cela permet de diminuer la taille des fichiers. Ainsi, les navigateurs lisent plus rapidement les fichiers.</p>

					<p>La concaténation consiste à regrouper en un seul, un ensemble de fichiers. Cela a pour effet de diminuer le nombre de requêtes HTTP.</p>
					<h2>Que dois-je faire si WP Rocket déforme l’affichage de mon site ?</h2>
					<p>Il y a de fortes chances que la déformation soit provoquée par la minification des fichiers HTML, CSS et/ou JavaScript. Pour résoudre le problème, nous conseillons de regarder la vidéo suivante : <a href="http://www.youtube.com/embed/iziXSvZgxLk" class="fancybox">http://www.youtube.com/embed/iziXSvZgxLk</a>.</p>

					<h2>À quel intervalle le cache est mis à jour ?</h2>
					<p>Le cache est automatiquement rafraîchit à chaque mise à jour d'un contenu (ajout/édition/suppression d’un article, publication d’un commentaire, etc...).</p>
					<p>Dans les options de base, vous pouvez aussi spécifier un délai de purge automatique du cache.</p>

					<h2>Comment ne pas mettre en cache une page particulière ?</h2>
					<p>Dans les options avancées, il est possible de spécifier des URLs à ne pas mettre en cache. Pour cela, il faut ajouter dans le champ de saisie <code>Ne jamais mettre en cache les pages suivantes</code> les URLs à exclure.</p>

					<h2>Comment fonctionne les robots de préchargement des fichiers de cache ?</h2>
					<p>Pour mettre une page en cache, il faut un premier visiteur. Pour éviter qu’un premier visiteur le fasse, nous avons développé deux robots (en python) qui crawl les pages de votre site Internet.</p>

					<p>Le premier va visiter votre site à la demande à l’aide du bouton “Précharger le cache”. Le second va automatiquement visiter votre site dès que vous allez créer/éditer/supprimer un article.</p>

					<p>Pour plus d’informations, vous pouvez consulter notre vidéo à ce propos : <a href="http://www.youtube.com/embed/9jDcg2f-9yM" class="fancybox">http://www.youtube.com/embed/9jDcg2f-9yM</a>.</p>
				</div>
			<?php endif; ?>
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
	$ext = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
	return $ext=='js' ? $file : false;
}



/**
 * Used to clean and sanitize the settings fields
 *
 * @since 1.0
 *
 */

function rocket_settings_callback( $inputs )
{
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

	$inputs['purge_cron_interval'] = 	isset( $inputs['purge_cron_interval'] ) ? (int)$inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );
	$inputs['purge_cron_unit'] = 		isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );
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
 * @since 1.0
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
 * @since 1.0
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