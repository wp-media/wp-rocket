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
{
?>
	<fieldset>
		<legend class="screen-reader-text"><span><?php _e( '<strong>JS</strong> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>
		<div id="rktdrop">

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

				<div class="rktdrag">

					<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Wait until this file is loaded ?', 'rocket' ); ?>
					</label>
					<span class="rkt-delete hide-if-no-js rkt-cross"><?php _e( 'Delete' ); ?></span>

				</div>

				<?php }
			}
			else
			{
				// If no files yet, use this template inside #rktdrop
				?>

				<div class="rktdrag">

					<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Wait until this file is loaded ?', 'rocket' ); ?>
					</label>

				</div>

			<?php } ?>

		</div>

		<?php // Clone Template ?>

		<div class="rktmodel rktdrag hide-if-js">

			<img class="rktmove hide-if-no-js" src="<?php echo WP_ROCKET_ADMIN_IMG_URL . 'icon-move.png'; ?>" width="16" heigth="16" alt="<?php _e( 'Move' ); ?>" title="<?php _e( 'Move' ); ?>" />
			<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />
			<label><input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][]" value="1" /> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?></label>

		</div>

		<p><a href="javascript:void(0)" id="rktclone" class="hide-if-no-js button-secondary"><?php _e( 'Add an URL', 'rocket' ); ?></a></p>
		<p class="description"><?php _e( 'You can add JavaScript files that will be loaded asynchronously at the same time as the page loads.', 'rocket' ); ?></p>
		<p class="hide-if-js"><?php _e( 'Empty the field to remove it.', 'rocket' ); ?></p>
		<p class="description"><?php _e( '<strong>Warning :</strong> you must specify the complete URL of the files.', 'rocket' ); ?></p>
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
		'rocket_min_js',
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
				'description'  => sprintf( __( 'Minification removes any space and comments present in the CSS and Javascript files.<br/>This mechanism reduces the weight of each file and allow a faster reading of browsers and search engines.<br/>Concatenation combines all CSS and Javascript files.<br/>This reduces the number of HTTP requests and improves the loading time.<br/><strong style="color:#FF0000;">Warning: concatenating files can cause display errors. In case of any errors we recommend that you disable this option or watch the following videos: <a href="%s" class="fancybox">%s</a></strong>.' ,'rocket' ), 'http:// www.youtube.com/embed/ziXSvZgxLk', 'http:// www.youtube.com/embed/ziXSvZgxLk' ) )
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
				'description'  => __( 'By default purge time is 12 hours, this means that once created, the cache files are automatically removed after 4 hours before being recreated. <br/> This can be useful if you display your latest tweets or rss feeds in your sidebar, for example. <br/> Specify 0 for unlimited life.', 'rocket' ),
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
			'description'  => sprintf( __( 'DNS prefetching is a way for browsers to anticipate the DNS resolution of external domains from your site.<br/>This mechanism reduces the latency of some external files.<br/>To Learn more about this option and how to use it correctly, we advise you to watch the following video: <a href="%s">%s</a><br/><strong>Warning:</strong> Enter the domain names without their protocol, for example: <code>//ajax.googleapis.com</code> without <code>http:</code> (one per line).', 'rocket' ), '', '' )
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
		<?php submit_button(); ?>
		<h2 class="nav-tab-wrapper hide-if-no-js">
			<?php if( rocket_valid_key() ) : ?>
				<a href="#tab_basic" class="nav-tab"><?php _e( 'Basic options', 'rocket' ); ?></a>
				<a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced options', 'rocket' ); ?></a>
				<a href="#tab_tools" class="nav-tab"><?php _e( 'Tools', 'rocket' ); ?></a>
				<?php
				if( WPLANG == 'fr_FR' ) { ?>
				
				<a href="#tab_tutos" class="nav-tab"><?php _e( 'Tutorials', 'rocket' ); ?></a>
				<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
				
				<?php
				}
				?>
				
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
				<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'tools' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_tutos">
					<h2><?php _e( 'Preload cache', 'rocket' ); ?></h2>
					<p><?php _e ( 'This video gives some explanations about our two crawler robots. They generate several cache files in a few seconds.', 'rocket' );?></p>
					<p><a href="http://www.youtube.com/embed/9jDcg2f-9yM" class="button-primary fancybox"><?php _e( 'Watch the video', 'rocket' ); ?></a></p>

					<h2><?php _e( 'CSS and JavaScript minification', 'rocket' );?></h2>
					<p>Cette vidéo donne quelques explications sur l’utilisation avancée du processus de minification et concaténation des fichiers CSS et JavaScript.</p>
					<p><a href="http://www.youtube.com/embed/iziXSvZgxLk" class="button-primary fancybox"><?php _e( 'Watch the video', 'rocket' ); ?></a></p>
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

					<h2>WP Rocket est-il compatible avec WP Touch, WordPress Mobile Pack et WP Mobile Detector ?</h2>
					<p>Oui.</p>
					<p>Par contre, dans les options de base, vous devez décocher la case <code>Activer la mise en cache pour les appareils mobile</code>.</p>

					<h2>WP Rocket est-il compatible avec WooCommerce ?</h2>
					<p>Oui.</p>

					<p>Cependant, il faut exclure les pages panier et commande de la mise en cache. Cela se fait à partir de l’option avancée <code>Ne jamais mettre en cache les pages suivantes</code> et en ajoutant les valeurs suivantes :</p>
					<p><code>/panier/<br/>
					/commande/(.*)
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
				<div class="rkt-tab rkt-tab-txt" id="tab_support">
					<p><?php _e('If none of the FAQ answers, answer your problem, you can tell us your issue on our <a href="http://support.wp-rocket.me/" target="_blank">Support</a>. We will reply as soon as possible.', 'rocket');?></p>
					<p><a href="http://support.wp-rocket.me/" class="button-primary" target="_blank"><?php _e('Go to Support');?></a></p>
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
 * Used to clean and sanitize the settings fields
 *
 * @since 1.0
 *
 */

function rocket_settings_callback( $inputs )
{
	// Clean inputs
	$inputs['dns_prefetch'] = 		isset( $inputs['dns_prefetch'] ) ? 	array_unique( array_filter( array_map( 'esc_url', 					array_map( 'trim', explode( "\n", $inputs['dns_prefetch'] ) ) ) ) ) 		: array();
	$inputs['cache_purge_pages'] = 		isset( $inputs['cache_purge_pages'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					array_map( 'trim', explode( "\n", $inputs['cache_purge_pages'] ) ) ) ) ) )		: array();
	$inputs['cache_reject_uri'] = 		isset( $inputs['cache_reject_uri'] ) ? 		array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'esc_url', 					array_map( 'trim', explode( "\n", $inputs['cache_reject_uri'] ) ) ) ) ) )		: array();
	$inputs['cache_reject_cookies'] = 	isset( $inputs['cache_reject_cookies'] ) ? 	array_unique( array_filter( array_map( 'rocket_clean_exclude_file',	array_map( 'sanitize_key', 				array_map( 'trim', explode( "\n", $inputs['cache_reject_cookies'] ) ) ) ) ) )	: array();
	$inputs['exclude_css'] = 			isset( $inputs['exclude_css'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_css', 		array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_css'] ) ) ) ) ) )			: array();
	$inputs['exclude_js'] = 			isset( $inputs['exclude_js'] ) ? 			array_unique( array_filter( array_map( 'rocket_sanitize_js', 		array_map( 'rocket_clean_exclude_file',	array_map( 'trim', explode( "\n", $inputs['exclude_js']) ) ) ) ) )				: array();
	$inputs['deferred_js_files'] = 		isset( $inputs['deferred_js_files'] ) ? 	array_filter( 				array_map( 'rocket_sanitize_js', 		array_unique( $inputs['deferred_js_files'] ) ) ) : array();
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

	// Update config file
	rocket_generate_config_file();
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