<?php
defined( 'ABSPATH' ) or	die( 'Cheatin&#8217; uh?' );

/**
 * Fix the capability for our capacity filter hook
 *
 * @since 2.6
 */
add_filter( 'option_page_capability_wp_rocket', 'rocket_correct_capability_for_options_page' );
function rocket_correct_capability_for_options_page( $capability ) {
	return apply_filters( 'rocket_capacity', 'manage_options' );
}

/**
 * Add submenu in menu "Settings"
 *
 * @since 1.0
 */
add_action( 'admin_menu', 'rocket_admin_menu' );
function rocket_admin_menu() {
	// do not use WP_ROCKET_PLUGIN_NAME here because if the WL has just been activated, the constant is not correct yet
	$wl_plugin_name = get_rocket_option( 'wl_plugin_name', WP_ROCKET_PLUGIN_NAME );

	// same with WP_ROCKET_PLUGIN_SLUG
	$wl_plugin_slug = sanitize_key( $wl_plugin_name );

	add_options_page( $wl_plugin_name, $wl_plugin_name, apply_filters( 'rocket_capacity', 'manage_options' ), $wl_plugin_slug, 'rocket_display_options' );
}

/**
 * Used to display fields on settings form
 *
 * @since 1.0
 */
function rocket_field( $args ) {
	if( ! is_array( reset( $args ) ) ) {
		$args = array( $args );
	}

	$full = $args;

	foreach ( $full as $args ) {
		if ( isset( $args['display'] ) && !$args['display'] ) {
			continue;
		}
		$args['label_for'] 	= isset( $args['label_for'] ) ? $args['label_for'] : '';
		$args['name'] 		= isset( $args['name'] ) ? $args['name'] : $args['label_for'];
		$parent 			= isset( $args['parent'] ) ? 'data-parent="' . sanitize_html_class( $args['parent' ] ). '"' : null;
		$placeholder 		= isset( $args['placeholder'] ) ? 'placeholder="'. $args['placeholder'].'" ' : '';
		$class				= isset( $args['class'] ) ? sanitize_html_class( $args['class'] ) : sanitize_html_class( $args['name'] );
		$class 				.= ( $parent ) ? ' has-parent' : null;
		$label 				= isset( $args['label'] ) ? $args['label'] : '';
		$default			= isset( $args['default'] ) ? $args['default'] : '';
		$readonly			= isset( $args['readonly'] ) && $args['readonly'] ? ' readonly="readonly" disabled="disabled"' : '';
		$cols 				= isset( $args['cols'] ) ? (int) $args['cols'] : 50;
		$rows 				= isset( $args['rows'] ) ? (int) $args['rows'] : 5;
		
		if( ! isset( $args['fieldset'] ) || 'start' == $args['fieldset'] ){
			echo '<fieldset class="fieldname-'.sanitize_html_class( $args['name'] ).' fieldtype-'.sanitize_html_class( $args['type'] ).'">';
		}

		switch( $args['type'] ) {
			case 'number' :
			case 'email' :
			case 'text' :

				$value = get_rocket_option( $args['name'] );
				if ( $value === false ) {
					$value = $default;
				}
				
				$value 			= esc_attr( $value );
				$number_options = $args['type']=='number' ? ' min="0" class="small-text"' : '';
				$autocomplete 	= in_array( $args['name'], array( 'consumer_key', 'consumer_email' ) ) ? ' autocomplete="off"' : '';
				$disabled 		= ( 'consumer_key' == $args['name'] && defined( 'WP_ROCKET_KEY' ) ) || ( 'consumer_email' == $args['name'] && defined( 'WP_ROCKET_EMAIL' ) ) ? ' disabled="disabled"' : $readonly;
				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><input<?php echo $autocomplete . $disabled; ?> type="<?php echo $args['type']; ?>"<?php echo $number_options; ?> id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?></label>

				<?php
			break;

            case 'cloudflare_api_key' :
                $value = get_rocket_option( $args['name'] );
			    	
			    	if ( 'cloudflare_api_key' == $args['name'] && defined( 'WP_ROCKET_CF_API_KEY' ) ) {
			    		$value = WP_ROCKET_CF_API_KEY;
			    	} 
			    	
			    	$value 			= esc_attr( $value );
			    	$disabled 		= ( 'cloudflare_api_key' == $args['name'] && defined( 'WP_ROCKET_CF_API_KEY' ) ) ? ' disabled="disabled"' : $readonly;
			    	$cf_valid_credentials = false;
			    	if ( function_exists( 'rocket_cloudflare_valid_auth' ) ) {
    			    	$cf_valid_credentials = ( is_wp_error( rocket_cloudflare_valid_auth() ) ) ? false : true;
			    	}
			    	?>
                
			    		<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
			    		<label>
			    		    <input<?php echo $disabled; ?> type="text" id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="<?php echo $value; ?>" <?php echo $placeholder; ?><?php echo $readonly; ?>/> <?php echo $label; ?>
			    		    <?php if ( $cf_valid_credentials ) { ?>
    			    		    <span id="rocket-check-cloudflare-api-container" class="rocket-cloudflare-api-valid">
								    <span class="dashicons dashicons-yes"></span> <?php _e( 'Your CloudFlare credentials are valid.', 'rocket' ); ?>
								</span>
			    		    <?php } elseif ( ! $cf_valid_credentials && $value ) { ?>
    			    		    <span id="rocket-check-cloudflare-api-container">
								    <span class="dashicons dashicons-no"></span> <?php _e( 'Your CloudFlare credentials are invalid!', 'rocket' ); ?>
                                    </span>
			    		    <?php }

			    		    if ( ! $value ) { ?>
    			    		    <p class="description"><?php printf( __( '<strong>Note:</strong> Where do I find my CloudFlare API key? <a href="%s" target="_blank">Learn more</a>', 'rocket' ), 'https://support.cloudflare.com/hc/en-us/articles/200167836-Where-do-I-find-my-CloudFlare-API-key-' ); ?></p>
			    		    <?php } ?>
                        </label>

				<?php
			break;
                

			case 'textarea' :

				$t_temp = get_rocket_option( $args['name'], '' );
				$value = ! empty( $t_temp ) ? esc_textarea( implode( "\n" , $t_temp ) ) : '';
				if ( ! $value ){
					$value = $default;
				}
				?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label><textarea id="<?php echo $args['label_for']; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" cols="<?php echo $cols; ?>" rows="<?php echo $rows; ?>"<?php echo $readonly; ?>><?php echo $value; ?></textarea>
					</label>

				<?php
			break;

			case 'checkbox' : 
				if ( isset( $args['label_screen'] ) ) { ?>
                    <legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
				<?php } ?>
					<label><input type="checkbox" id="<?php echo $args['name']; ?>" class="<?php echo $class; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]" value="1"<?php echo $readonly; ?> <?php checked( get_rocket_option( $args['name'], $default ), 1 ); ?> <?php echo $parent; ?>/> <?php echo $args['label']; ?>
					</label>

			<?php
			break;

			case 'select' : ?>

					<legend class="screen-reader-text"><span><?php echo $args['label_screen']; ?></span></legend>
					<label>	<select id="<?php echo $args['name']; ?>" class="<?php echo $class; ?>" name="wp_rocket_settings[<?php echo $args['name']; ?>]"<?php echo $readonly; ?> <?php echo $parent; ?>>
							<?php foreach( $args['options'] as $val => $title) { ?>
								<option value="<?php echo $val; ?>" <?php selected( get_rocket_option( $args['name'] ), $val ); ?>><?php echo $title; ?></option>
							<?php } ?>
							</select>
					<?php echo $label; ?>
					</label>

			<?php
			break;

            case 'submit_optimize' : ?>

            <input type="submit" name="wp_rocket_settings[submit_optimize]" id="rocket_submit_optimize" class="button button-primary" value="<?php _e( 'Save and optimize', 'rocket' ); ?>"> <a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_optimize_database' ), 'rocket_optimize_database' ); ?>" class="button button-secondary"><?php _e( 'Optimize', 'rocket' ); ?></a>
            <?php break;

			case 'repeater' :

				$fields = new WP_Rocket_Repeater_Field( $args );
				$fields->render();

				break;

			case 'rocket_defered_module' :

					rocket_defered_module();

			break;

			case 'helper_description' :

				$description = isset( $args['description'] ) ? '<p class="description desc '.$class.'" ' . $parent . '>'.$args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'description' );

			break;

			case 'helper_help' :

				$description = isset( $args['description'] ) ? '<p class="description help ' . $class . '" ' . $parent . '>'.$args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'help' );

			break;

			case 'helper_warning' :

				$description = isset( $args['description'] ) ? '<p class="description warning file-error ' . $class . '" ' . $parent . '><b>'.__( 'Warning: ', 'rocket') . '</b>' . $args['description'].'</p>' : '';
				echo apply_filters( 'rocket_help', $description, $args['name'], 'warning' );

			break;

			case 'rocket_export_form' :
				?>
				<a href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_export' ), 'rocket_export' ); ?>" id="export" class="button button-secondary rocketicon"><?php _e( 'Download options', 'rocket' ); ?></a>
				<?php
			break;

			case 'rocket_import_upload_form' :

				rocket_import_upload_form( 'rocket_importer' );

			break;
			default : 'Type manquant ou incorrect'; // ne pas traduire

		}

		if( ! isset( $args['fieldset'] ) || 'end' == $args['fieldset'] ) {
			echo '</fieldset>';
		}

	}

}

/**
 * Used to display the defered module on settings form
 *
 * @since 1.1.0
 */
function rocket_defered_module() { ?>
	<fieldset>
	<legend class="screen-reader-text"><span><?php _e( '<b>JS</b> files with Deferred Loading JavaScript', 'rocket' ); ?></span></legend>

	<div id="rkt-drop-deferred" class="rkt-module rkt-module-drop">

		<?php
		$deferred_js_files = get_rocket_option( 'deferred_js_files' );
		$deferred_js_wait = get_rocket_option( 'deferred_js_wait' );

		if( $deferred_js_files ) {

			foreach( $deferred_js_files as $k=>$_url ) {

				$checked = isset( $deferred_js_wait[$k] ) ? checked( $deferred_js_wait[$k], '1', false ) : ''; ?>

				<p class="rkt-module-drag">
					<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

					<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][<?php echo $k; ?>]" value="<?php echo esc_url( $_url ); ?>" />

					<label>
						<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][<?php echo $k; ?>]" value="1" <?php echo $checked; ?>/> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
					</label>

					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
				</p>
				<!-- .rkt-module-drag -->

			<?php
			}

		} else {
			// If no files yet, use this template inside #rkt-drop-deferred
			?>

			<p class="rkt-module-drag">
				<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

				<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][0]" value="" />

				<label>
					<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][0]" value="1" /> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
				</label>
			</p>
			<!-- .rkt-module-drag -->

		<?php } ?>

	</div>
	<!-- .rkt-drop-deferred -->

	<?php // Clone Template ?>

	<div class="rkt-module-model hide-if-js">

		<p class="rkt-module-drag">
			<span class="dashicons dashicons-sort rkt-module-move hide-if-no-js"></span>

			<input style="width: 32em" type="text" placeholder="http://" class="deferred_js regular-text" name="wp_rocket_settings[deferred_js_files][]" value="" />

			<label>
				<input type="checkbox" class="deferred_js" name="wp_rocket_settings[deferred_js_wait][]" value="1" /> <?php _e( 'Wait until this file is loaded?', 'rocket' ); ?>
			</label>
			<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>
		</p>
		<!-- .rkt-module-drag -->
	</div>
	<!-- .rkt-model-deferred-->

	<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add URL', 'rocket' ); ?></a></p>

<?php
}

/**
 * Used to display the CNAMES module on settings form
 *
 * @since 2.1
 */
function rocket_cnames_module() { ?>
		<legend class="screen-reader-text"><span><?php _e( 'Replace site\'s hostname with:', 'rocket' ); ?></span></legend>

		<div id="rkt-cnames" class="rkt-module">

			<?php

			$cnames = get_rocket_option( 'cdn_cnames' );
			$cnames_zone = get_rocket_option( 'cdn_zone' );

			if( $cnames ) {

				foreach( $cnames as $k=>$_url ) { ?>

				<p>

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][<?php echo $k; ?>]" value="<?php echo esc_attr( $_url ); ?>" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][<?php echo $k; ?>]">
							<option value="all" <?php selected( $cnames_zone[$k], 'all' ); ?>><?php _e( 'All files', 'rocket' ); ?></option>
							<option value="images" <?php selected( $cnames_zone[$k], 'images' ); ?>><?php _e( 'Images', 'rocket' ); ?></option>
							<option value="css_and_js" <?php selected( $cnames_zone[$k], 'css_and_js' ); ?>>CSS & JavaScript</option>
							<option value="js" <?php selected( $cnames_zone[$k], 'js' ); ?>>JavaScript</option>
							<option value="css" <?php selected( $cnames_zone[$k], 'css' ); ?>>CSS</option>
						</select>
					</label>
					<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

				</p>

				<?php
				}

			} else {

				// If no files yet, use this template inside #rkt-cnames
				?>

				<p>

					<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

					<label>
						<?php _e( 'reserved for', 'rocket' ); ?>
						<select name="wp_rocket_settings[cdn_zone][]">
							<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
							<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
							<option value="css_and_js">CSS & JavaScript</option>
							<option value="js">JavaScript</option>
							<option value="css">CSS</option>
						</select>
					</label>

				</p>

			<?php } ?>

		</div>

		<?php // Clone Template ?>
		<div class="rkt-module-model hide-if-js">

			<p>

				<input style="width: 32em" type="text" placeholder="http://" class="regular-text" name="wp_rocket_settings[cdn_cnames][]" value="" />

				<label>
					<?php _e( 'reserved for', 'rocket' ); ?>
					<select name="wp_rocket_settings[cdn_zone][]">
						<option value="all"><?php _e( 'All files', 'rocket' ); ?></option>
						<option value="images"><?php _e( 'Images', 'rocket' ); ?></option>
						<option value="css_and_js">CSS & JavaScript</option>
						<option value="js">JavaScript</option>
						<option value="css">CSS</option>
					</select>
				</label>
				<span class="dashicons dashicons-no rkt-module-remove hide-if-no-js"></span>

			</p>

		</div>

		<p><a href="javascript:void(0)" class="rkt-module-clone hide-if-no-js button-secondary"><?php _e( 'Add CNAME', 'rocket' ); ?></a></p>

	</fieldset>

<?php
}

/**
 * Used to display buttons on settings form, tools tab
 *
 * @since 1.1.0
 */
function rocket_button( $args ) {
	$button       = $args['button'];
	$desc         = isset( $args['helper_description'] ) ? $args['helper_description'] : null;
	$help         = isset( $args['helper_help'] ) ? $args['helper_help'] : null;
	$warning      = isset( $args['helper_warning'] ) ? $args['helper_warning'] : null;
	$id           = isset( $button['button_id'] ) ? sanitize_html_class( $button['button_id'] ) : null;
	$class        = sanitize_html_class( strip_tags( $button['button_label'] ) );
	$button_style = isset( $button['style'] ) ? 'button-'.sanitize_html_class( $button['style'] ) : 'button-secondary';

	if ( ! empty( $help ) ) {
		$help = '<p class="description help '.$class.'">'.$help['description'].'</p>';
	}
	if ( ! empty( $desc ) ) {
		$desc = '<p class="description desc '.$class.'">'.$desc['description'].'</p>';
	}
	if ( ! empty( $warning ) ) {
		$warning = '<p class="description warning file-error '.$class.'"><b>'.__( 'Warning: ', 'rocket' ) . '</b>' . $warning['description'].'</p>';
	}
?>
	<fieldset class="fieldname-<?php echo $class; ?> fieldtype-button">
		<?php
		if ( isset( $button['url'] ) ) {
			echo '<a href="' . esc_url( $button['url'] ) . '" id="' . $id . '" class="' . $button_style . ' rocketicon rocketicon-'. $class . '">' . wp_kses_post( $button['button_label'] ) . '</a>';
		} else {
			echo '<button id="' . $id . '" class="' . $button_style . ' rocketicon rocketicon-'. $class . '">' . wp_kses_post( $button['button_label'] ) . '</button>';
		}
		?>
		

		<?php echo apply_filters( 'rocket_help', $desc, sanitize_key( strip_tags( $button['button_label'] ) ), 'description' ); ?>
		<?php echo apply_filters( 'rocket_help', $help, sanitize_key( strip_tags( $button['button_label'] ) ), 'help' ); ?>
		<?php echo apply_filters( 'rocket_help', $warning, sanitize_key( strip_tags( $button['button_label'] ) ), 'warning' ); ?>

	</fieldset>
<?php
}

/**
 * Used to display videos buttons on settings form
 *
 * @since 2.2
 */
function rocket_video( $args ) {
	$desc = '<p class="description desc '.sanitize_html_class( $args['name'] ).'">'.$args['description'].'</p>';
?>
	<fieldset class="fieldname-<?php echo $args['name']; ?> fieldtype-button">
		<a href="<?php echo esc_url( $args['url'] ); ?>" class="button-secondary fancybox rocketicon rocketicon-video"><?php _e( 'Watch the video', 'rocket' ); ?></a>
		<?php echo apply_filters( 'rocket_help', $desc, $args['name'], 'description' ); ?>
	</fieldset>
<?php
}

/**
 * Used to include a file in any tab
 *
 * @since 2.2
 */
function rocket_include( $args ) {
	include_once( dirname( __FILE__ ) . '/' . str_replace( '..', '', $args['file'] ) . '.inc.php' );
}

/**
 * The main settings page construtor using the required functions from WP
 * @since 1.1.0 Add tabs, tools tab and change options severity
 * @since 1.0
 */
function rocket_display_options() {
	$modules = array(
		'api-key',
		'basic',
		'advanced',
		'database',
		'preload',
		'cloudflare',
		'cdn',
		'varnish',
		'white-label',
		'tools',
		'tutorials',
		'faq',
		'support'
	);
	
	foreach( $modules as $module ) {
		require( WP_ROCKET_ADMIN_UI_MODULES_PATH . $module . '.php' );
	}
	
	$heading_tag = version_compare( $GLOBALS['wp_version'], '4.3' ) >= 0 ? 'h1' : 'h2';
	?>
	
	<div class="wrap">

	<<?php echo $heading_tag; ?>><?php echo WP_ROCKET_PLUGIN_NAME; ?> <small><sup><?php echo WP_ROCKET_VERSION; ?></sup></small></<?php echo $heading_tag; ?>>
	<form action="options.php" id="rocket_options" method="post" enctype="multipart/form-data">
		<?php 
		settings_fields( 'wp_rocket' );
		
		rocket_hidden_fields( 
			array( 
				'consumer_key', 
				'consumer_email', 
				'secret_key', 
				'license', 
				'secret_cache_key',
				'minify_css_key', 
				'minify_js_key', 
				'version', 
				'cloudflare_old_settings',
				'cloudflare_zone_id'
			)
		); 
				
		submit_button(); 
		?>
		<h2 class="nav-tab-wrapper hide-if-no-js">
			<?php if( rocket_valid_key() ) { ?>
				<a href="#tab_basic" class="nav-tab"><?php _e( 'Basic options', 'rocket' ); ?></a>
				<a href="#tab_advanced" class="nav-tab"><?php _e( 'Advanced options', 'rocket' ); ?></a>
				<a href="#tab_database" class="nav-tab"><?php _e( 'Database', 'rocket' ); ?></a>
				<a href="#tab_preload" class="nav-tab"><?php _e( 'Preload', 'rocket' ); ?></a>
				<?php if ( get_rocket_option( 'do_cloudflare' ) ) { ?>
					<a href="#tab_cloudflare" class="nav-tab">CloudFlare</a>
				<?php } ?>
				<a href="#tab_cdn" class="nav-tab"><?php _e( 'CDN', 'rocket' ); ?></a>
				<?php 
				/** This filter is documented in inc/admin/ui/modules/vanrish.php */	
				if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) { ?>
				<a href="#tab_varnish" class="nav-tab">Varnish</a>
				<?php } ?>
				<?php if( defined( 'WP_RWL' ) ) { ?>
					<a href="#tab_whitelabel" class="nav-tab"><?php _e( 'White Label', 'rocket' ); ?></a>
				<?php } ?>
				<a href="#tab_tools" class="nav-tab"><?php _e( 'Tools', 'rocket' ); ?></a>
				<?php if ( ! rocket_is_white_label() ) { ?>
					<?php if( defined( 'WPLANG' ) && 'fr_FR' == WPLANG || 'fr_FR' == get_locale() ) { ?>
						<a href="#tab_tutorials" class="nav-tab"><?php _e( 'Tutorials', 'rocket' ); ?></a>
					<?php } ?>
					<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
					<a href="#tab_support" class="nav-tab"><?php _e( 'Support', 'rocket' ); ?></a>
				<?php } ?>
			<?php }else{ ?>
				<a href="#tab_apikey" class="nav-tab"><?php _e( 'License', 'rocket' ); ?></a>
				<a href="#tab_faq" class="nav-tab"><?php _e( 'FAQ', 'rocket' ); ?></a>
				<a href="#tab_support" class="nav-tab file-error"><?php _e( 'Support', 'rocket' ); ?></a>
			<?php }  ?>
			<?php
			do_action( 'rocket_tab', rocket_valid_key() );
			?>
		</h2>
		<div id="rockettabs">
			<?php if( rocket_valid_key() ) { ?>
				<div class="rkt-tab" id="tab_basic"><?php do_settings_sections( 'rocket_basic' ); ?></div>
				<div class="rkt-tab" id="tab_advanced"><?php do_settings_sections( 'rocket_advanced' ); ?></div>
				<div class="rkt-tab" id="tab_database">
    				<p class="description database_description"><?php _e( 'The following options help you optimize your database.', 'rocket' ); ?></p>
                    <p class="description warning file-error database_description"><?php _e( 'Before you do any optimization, please backup your database first because any cleanup done is irreversible!', 'rocket' ); ?></p>
    				<?php do_settings_sections( 'rocket_database' ); ?>
				</div>
				<div class="rkt-tab" id="tab_preload"><?php do_settings_sections( 'rocket_preload' ); ?></div>
				<div class="rkt-tab" id="tab_cloudflare" <?php echo get_rocket_option( 'do_cloudflare' ) ? '' : 'style="display:none"'; ?>><?php do_settings_sections( 'rocket_cloudflare' ); ?></div>
				<div class="rkt-tab" id="tab_cdn"><?php do_settings_sections( 'rocket_cdn' ); ?></div>
				<?php 
				/** This filter is documented in inc/admin/ui/modules/vanrish.php */	
				if ( apply_filters( 'rocket_display_varnish_options_tab', true ) ) { ?>
					<div class="rkt-tab" id="tab_varnish">
						<p class="description varnish_description"><?php _e( 'The following options are for hosting with Varnish cache system.', 'rocket' ); ?><br/>
						<?php _e( 'If you don’t know if Varnish is installed on your server, you can ignore these settings.', 'rocket' ); ?></p>
						<?php do_settings_sections( 'rocket_varnish' ); ?>
					</div>
				<?php } ?>
				<?php $class_hidden = !defined( 'WP_RWL' ) ? ' hidden' : ''; ?>
				<div class="rkt-tab<?php echo $class_hidden; ?>" id="tab_whitelabel"><?php do_settings_sections( 'rocket_white_label' ); ?></div>
				<div class="rkt-tab" id="tab_tools"><?php do_settings_sections( 'rocket_tools' ); ?></div>
				<?php if ( ! rocket_is_white_label() ) { ?>
					<div class="rkt-tab" id="tab_tutorials"><?php do_settings_sections( 'rocket_tutorials' ); ?></div>
					<div class="rkt-tab rkt-tab-txt" id="tab_faq"><?php do_settings_sections( 'rocket_faq' ); ?></div>
					<div class="rkt-tab rkt-tab-txt" id="tab_support"><?php do_settings_sections( 'rocket_support' ); ?></div>
				<?php } ?>
			<?php }else{ ?>
				<div class="rkt-tab" id="tab_apikey"><?php do_settings_sections( 'rocket_apikey' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_faq"><?php do_settings_sections( 'rocket_faq' ); ?></div>
				<div class="rkt-tab rkt-tab-txt" id="tab_support"><?php do_settings_sections( 'rocket_support' ); ?></div>
			<?php } ?>
			<?php
			do_action( 'rocket_tab_content', rocket_valid_key() );
			?>
		</div>
		<?php submit_button(); ?>
	</form>
<?php
}

/**
 * Tell to WordPress to be confident with our setting, we are clean!
 *
 * @since 1.0
 */
add_action( 'admin_init', 'rocket_register_setting' );
function rocket_register_setting() {
	register_setting( 'wp_rocket', WP_ROCKET_SLUG, 'rocket_settings_callback' );
}

/**
 * Used to clean and sanitize the settings fields
 *
 * @since 1.0
 */
function rocket_settings_callback( $inputs ) {
	if ( isset( $_GET['action'] ) && 'purge_cache' == $_GET['action'] ) {
		return $inputs;
	}

	/*
	 * Option : Minification CSS & JS
	 */
	$inputs['minify_css'] = ! empty( $inputs['minify_css'] ) ? 1 : 0;
	$inputs['minify_js']  = ! empty( $inputs['minify_js'] ) ? 1 : 0;

	/*
	 * Option : Purge delay
	 */
	$inputs['purge_cron_interval'] = isset( $inputs['purge_cron_interval'] ) ? (int)$inputs['purge_cron_interval'] : get_rocket_option( 'purge_cron_interval' );
	$inputs['purge_cron_unit'] = isset( $inputs['purge_cron_unit'] ) ? $inputs['purge_cron_unit'] : get_rocket_option( 'purge_cron_unit' );

    /*
	 * Option : Minification CSS & JS
	 */
	$inputs['remove_query_strings'] = ! empty( $inputs['remove_query_strings'] ) ? 1 : 0;

	/*
	 * Option : Prefetch DNS requests
	 */
	if ( ! empty( $inputs['dns_prefetch'] ) ) {
		if ( ! is_array( $inputs['dns_prefetch'] ) ) {
			$inputs['dns_prefetch'] = explode( "\n", $inputs['dns_prefetch'] );
		}
		$inputs['dns_prefetch'] = array_map( 'trim', $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = array_map( 'esc_url', $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = (array) array_filter( $inputs['dns_prefetch'] );
		$inputs['dns_prefetch'] = array_unique( $inputs['dns_prefetch'] );
	} else {
		$inputs['dns_prefetch'] = array();
	}

	/*
	 * Option : Empty the cache of the following pages when updating an article
	 */
	if ( ! empty( $inputs['cache_purge_pages'] ) ) {
		if ( ! is_array( $inputs['cache_purge_pages'] ) ) {
			$inputs['cache_purge_pages'] = explode( "\n", $inputs['cache_purge_pages'] );
		}
		$inputs['cache_purge_pages'] = array_map( 'trim', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_map( 'esc_url', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = (array) array_filter( $inputs['cache_purge_pages'] );
		$inputs['cache_purge_pages'] = array_unique( $inputs['cache_purge_pages'] );
	} else {
		$inputs['cache_purge_pages'] = array();
	}

	/*
	 * Option : Never cache the following pages
	 */
	if ( ! empty( $inputs['cache_reject_uri'] ) ) {
		if ( ! is_array( $inputs['cache_reject_uri'] ) ) {
			$inputs['cache_reject_uri'] = explode( "\n", $inputs['cache_reject_uri'] );
		}
		$inputs['cache_reject_uri'] = array_map( 'trim', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_map( 'esc_url', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_map( 'rocket_clean_exclude_file', $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = (array) array_filter( $inputs['cache_reject_uri'] );
		$inputs['cache_reject_uri'] = array_unique( $inputs['cache_reject_uri'] );
	} else {
		$inputs['cache_reject_uri'] = array();
	}

	/*
	 * Option : Don't cache pages that use the following cookies
	 */
	if ( ! empty( $inputs['cache_reject_cookies'] ) ) {
		if ( ! is_array( $inputs['cache_reject_cookies'] ) ) {
			$inputs['cache_reject_cookies'] = explode( "\n", $inputs['cache_reject_cookies'] );
		}
		$inputs['cache_reject_cookies'] = array_map( 'trim', $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = array_map( 'rocket_sanitize_key', $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = (array) array_filter( $inputs['cache_reject_cookies'] );
		$inputs['cache_reject_cookies'] = array_unique( $inputs['cache_reject_cookies'] );
	} else {
		$inputs['cache_reject_cookies'] = array();
	}

	/*
	 * Option : Cache pages that use the following query strings (GET parameters)
	 */
	if ( ! empty( $inputs['cache_query_strings'] ) ) {
		if ( ! is_array( $inputs['cache_query_strings'] ) ) {
			$inputs['cache_query_strings'] = explode( "\n", $inputs['cache_query_strings'] );
		}
		$inputs['cache_query_strings'] = array_map( 'trim', $inputs['cache_query_strings'] );
		$inputs['cache_query_strings'] = array_map( 'rocket_sanitize_key', $inputs['cache_query_strings'] );
		$inputs['cache_query_strings'] = (array) array_filter( $inputs['cache_query_strings'] );
		$inputs['cache_query_strings'] = array_unique( $inputs['cache_query_strings'] );
	} else {
		$inputs['cache_query_strings'] = array();
	}

	/*
	 * Option : Never send cache pages for these user agents
	 */
	if ( ! empty( $inputs['cache_reject_ua'] ) ) {
		if ( ! is_array( $inputs['cache_reject_ua'] ) ) {
			$inputs['cache_reject_ua'] = explode( "\n", $inputs['cache_reject_ua'] );
		}
		$inputs['cache_reject_ua'] = array_map( 'trim', $inputs['cache_reject_ua'] );
		$inputs['cache_reject_ua'] = array_map( 'rocket_sanitize_ua', $inputs['cache_reject_ua'] );
		$inputs['cache_reject_ua'] = (array) array_filter( $inputs['cache_reject_ua'] );
		$inputs['cache_reject_ua'] = array_unique( $inputs['cache_reject_ua'] );
	} else {
		$inputs['cache_reject_ua'] = array();
	}

	/*
	 * Option : CSS files to exclude of the minification
	 */
	if ( ! empty( $inputs['exclude_css'] ) ) {
		if ( ! is_array( $inputs['exclude_css'] ) ) {
			$inputs['exclude_css'] = explode( "\n", $inputs['exclude_css'] );
		}
		$inputs['exclude_css'] = array_map( 'trim', $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_map( 'rocket_sanitize_css', $inputs['exclude_css'] );
		$inputs['exclude_css'] = (array) array_filter( $inputs['exclude_css'] );
		$inputs['exclude_css'] = array_unique( $inputs['exclude_css'] );
	} else {
		$inputs['exclude_css'] = array();
	}

	/*
	 * Option : JS files to exclude of the minification
	 */
	if ( ! empty( $inputs['exclude_js'] ) ) {
		if ( ! is_array( $inputs['exclude_js'] ) ) {
			$inputs['exclude_js'] = explode( "\n", $inputs['exclude_js'] );
		}
		$inputs['exclude_js'] = array_map( 'trim', $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_map( 'rocket_clean_exclude_file', $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_map( 'rocket_sanitize_js', $inputs['exclude_js'] );
		$inputs['exclude_js'] = (array) array_filter( $inputs['exclude_js'] );
		$inputs['exclude_js'] = array_unique( $inputs['exclude_js'] );
	} else {
		$inputs['exclude_js'] = array();
	}


	/*
	 * Option : JS files with deferred loading
	 */
	if ( ! empty( $inputs['deferred_js_files'] ) ) {
		$inputs['deferred_js_files'] = array_unique( $inputs['deferred_js_files'] );
		$inputs['deferred_js_files'] = array_map( 'rocket_sanitize_js', $inputs['deferred_js_files'] );
		$inputs['deferred_js_files'] = array_filter( $inputs['deferred_js_files'] );
	} else {
		$inputs['deferred_js_files'] = array();
	}

	if ( ! $inputs['deferred_js_files'] ) {
		$inputs['deferred_js_wait'] = array();
	} else {
		for ( $i=0; $i<=max( array_keys( $inputs['deferred_js_files'] ) ); $i++ ) {
			if ( ! isset( $inputs['deferred_js_files'][$i] ) ) {
				unset( $inputs['deferred_js_wait'][$i] );
			} else {
				$inputs['deferred_js_wait'][$i] = isset( $inputs['deferred_js_wait'][$i] ) ? '1' : '0';
			}
		}

		$inputs['deferred_js_files'] = array_values( $inputs['deferred_js_files'] );
		ksort( $inputs['deferred_js_wait'] );
		$inputs['deferred_js_wait'] = array_values( $inputs['deferred_js_wait'] );
	}

	/*
	 * Option : JS files of the minification to insert in footer
	 */
	if ( ! empty( $inputs['minify_js_in_footer'] ) ) {
		foreach( $inputs['minify_js_in_footer'] as $k=>$url ) {
			if( in_array( $url, $inputs['deferred_js_files'] ) ) {
				unset( $inputs['minify_js_in_footer'][$k] );
			}
		}

		$inputs['minify_js_in_footer'] = array_filter( array_map( 'rocket_sanitize_js', array_unique( $inputs['minify_js_in_footer'] ) ) );
	} else {
		$inputs['minify_js_in_footer'] = array();
	}

    /**
     * Database options
     */
    $inputs['database_revisions']          = ! empty( $inputs['database_revisions'] ) ? 1 : 0;
    $inputs['database_auto_drafts']        = ! empty( $inputs['database_auto_drafts'] ) ? 1 : 0;
    $inputs['database_trashed_posts']      = ! empty( $inputs['database_trashed_posts'] ) ? 1 : 0;
    $inputs['database_spam_comments']      = ! empty( $inputs['database_spam_comments'] ) ? 1 : 0;
    $inputs['database_trashed_comments']   = ! empty( $inputs['database_trashed_comments'] ) ? 1 : 0;
    $inputs['database_expired_transients'] = ! empty( $inputs['database_expired_transients'] ) ? 1 : 0;
    $inputs['database_all_transients']     = ! empty( $inputs['database_all_transients'] ) ? 1 : 0;
    $inputs['database_optimize_tables']    = ! empty( $inputs['database_optimize_tables'] ) ? 1 : 0;
    $inputs['schedule_automatic_cleanup']  = ! empty( $inputs['schedule_automatic_cleanup'] ) ? 1 : 0;
    $inputs['automatic_cleanup_frequency'] = ! empty( $inputs['automatic_cleanup_frequency'] ) ? $inputs['automatic_cleanup_frequency'] : '';

    if ( $inputs['schedule_automatic_cleanup'] != 1 && ( 'daily' != $inputs['automatic_cleanup_frequency'] || 'weekly' != $inputs['automatic_cleanup_frequency'] || 'monthly' != $inputs['automatic_cleanup_frequency'] ) ) {
        unset( $inputs['automatic_cleanup_frequency'] );
    }
	
    /**
     * Options: Activate bot preload
     */
    $inputs['manual_preload']    = ! empty( $inputs['manual_preload'] ) ? 1 : 0;
    $inputs['automatic_preload'] = ! empty( $inputs['automatic_preload'] ) ? 1 : 0;

    /*
     * Option: activate sitemap preload
     */
    $inputs['sitemap_preload'] = ! empty( $inputs['sitemap_preload'] ) ? 1 : 0;

    /*
     * Option : XML sitemaps URLs
     */
    if ( ! empty( $inputs['sitemaps'] ) ) {
		if ( ! is_array( $inputs['sitemaps'] ) ) {
			$inputs['sitemaps'] = explode( "\n", $inputs['sitemaps'] );
		}
		$inputs['sitemaps'] = array_map( 'trim', $inputs['sitemaps'] );
		$inputs['sitemaps'] = array_map( 'rocket_sanitize_xml', $inputs['sitemaps'] );
		$inputs['sitemaps'] = (array) array_filter( $inputs['sitemaps'] );
		$inputs['sitemaps'] = array_unique( $inputs['sitemaps'] );
	} else {
		$inputs['sitemaps'] = array();
	}

	/*
	 * Option : CloudFlare Domain
	 */
	if ( ! empty( $inputs['cloudflare_domain'] ) ) {
		$inputs['cloudflare_domain'] = rocket_get_domain( $inputs['cloudflare_domain'] );
	} else {
		$inputs['cloudflare_domain'] = '';
	}

	/*
	 * Option : WL
	 */
	$inputs['wl_plugin_name'] = isset( $inputs['wl_plugin_name'] ) ? wp_strip_all_tags( $inputs['wl_plugin_name'] ) : get_rocket_option( 'wl_plugin_name' );
	$inputs['wl_plugin_URI']  = isset( $inputs['wl_plugin_URI'] )  ? esc_url( $inputs['wl_plugin_URI'] )            : get_rocket_option( 'wl_plugin_URI' );
	$inputs['wl_author']      = isset( $inputs['wl_author'] )      ? wp_strip_all_tags( $inputs['wl_author'] )      : get_rocket_option( 'wl_author' );
	$inputs['wl_author_URI']  = isset( $inputs['wl_author_URI'] )  ? esc_url( $inputs['wl_author_URI'] )            : get_rocket_option( 'wl_author_URI' );
	$inputs['wl_description'] = isset( $inputs['wl_description'] ) ? (array)$inputs['wl_description']               : get_rocket_option( 'wl_description' );
	$inputs['wl_plugin_slug'] = sanitize_key( $inputs['wl_plugin_name'] );
	
	/*
	 * Option : CloudFlare
	 */
	 
	 if ( defined( 'WP_ROCKET_CF_API_KEY' ) ) {
		 $inputs['cloudflare_api_key'] = get_rocket_option( 'cloudflare_api_key' );
	 }
	
	/*
	 * Option : CDN
	 */
	$inputs['cdn_cnames'] = isset( $inputs['cdn_cnames'] ) ? array_unique( array_filter( $inputs['cdn_cnames'] ) ) : array();

	if ( ! $inputs['cdn_cnames'] ) {
		$inputs['cdn_zone'] = array();
	} else {
		for ( $i = 0; $i <= max( array_keys( $inputs['cdn_cnames'] ) ); $i++ ) {
			if ( ! isset( $inputs['cdn_cnames'][ $i ] ) ) {
				unset( $inputs['cdn_zone'][ $i ] );
			} else {
				$inputs['cdn_zone'][ $i ] = isset( $inputs['cdn_zone'][ $i ] ) ? $inputs['cdn_zone'][ $i ] : 'all';
			}
		}

		$inputs['cdn_cnames'] 	= array_values( $inputs['cdn_cnames'] );
		$inputs['cdn_cnames']   = array_map( 'untrailingslashit', $inputs['cdn_cnames'] );
		ksort( $inputs['cdn_zone'] );
		$inputs['cdn_zone'] 	= array_values( $inputs['cdn_zone'] );
	}

	/*
	 * Option : Files to exclude of the CDN process
	 */
	if ( ! empty( $inputs['cdn_reject_files'] ) ) {
		if ( ! is_array( $inputs['cdn_reject_files'] ) ) {
			$inputs['cdn_reject_files'] = explode( "\n", $inputs['cdn_reject_files'] );
		}
		$inputs['cdn_reject_files'] = array_map( 'trim', $inputs['cdn_reject_files'] );
		$inputs['cdn_reject_files'] = array_map( 'rocket_clean_exclude_file', $inputs['cdn_reject_files'] );
		$inputs['cdn_reject_files'] = (array) array_filter( $inputs['cdn_reject_files'] );
		$inputs['cdn_reject_files'] = array_unique( $inputs['cdn_reject_files'] );
	} else {
		$inputs['cdn_reject_files'] = array();
	}
	
	/*
	 * Option: Support
	 */
	$fake_options = array( 
		'support_summary',
		'support_description',
		'support_documentation_validation'
	);
	
	foreach ( $fake_options as $option ) {
		 if( isset( $inputs[$option] ) ) {
			 unset($inputs[$option]);
		 }
	}
	
	$filename_prefix = rocket_is_white_label() ? sanitize_title( get_rocket_option( 'wl_plugin_name' ) ) : 'wp-rocket';
	 
	if ( isset( $_FILES['import'] )
		&& preg_match( '/'. $filename_prefix . '-settings-20\d{2}-\d{2}-\d{2}-[a-f0-9]{13}\.txt/', $_FILES['import']['name'] )
		&& 'text/plain' == $_FILES['import']['type'] ) {
		$file_name 			= $_FILES['import']['name'];
		$_POST_action 		= $_POST['action'];
		$_POST['action'] 	= 'wp_handle_sideload';
		$file 				= wp_handle_sideload( $_FILES['import'], array( 'mimes' => array( 'txt' => 'application/octet-stream' ) ) );
		$_POST['action'] 	= $_POST_action;
		$gz 				= 'gz'.strrev( 'etalfni' );
		$settings 			= @file_get_contents( $file['file'] );
		$settings 			= $gz//;
		( $settings );
		$settings 			= unserialize( $settings );
		file_put_contents( $file['file'], '' );
		@unlink( $file['file'] );
		if ( is_array( $settings ) ) {
			$settings['consumer_key']		= $inputs['consumer_key'];
			$settings['consumer_email']		= $inputs['consumer_email'];
			$settings['secret_key']			= $inputs['secret_key'];
			$settings['secret_cache_key']	= $inputs['secret_cache_key'];
			$settings['minify_css_key']		= $inputs['minify_css_key'];
			$settings['minify_js_key']		= $inputs['minify_js_key'];
			$settings['version']			= $inputs['version'];
			$inputs = $settings;
			add_settings_error( 'general', 'settings_updated', __( 'Settings imported and saved.', 'rocket' ), 'updated' );
		}
	}

	if ( ! rocket_valid_key() ) {
		$checked = rocket_check_key( 'live' );
	} else {
		$checked = rocket_check_key( 'transient_1' );
	}

	if ( is_array( $checked ) ) {
		$inputs['consumer_key'] = $checked['consumer_key'];
		$inputs['consumer_email'] = $checked['consumer_email'];
		$inputs['secret_key'] = $checked['secret_key'];
	}

	if ( rocket_valid_key() && ! empty( $inputs['secret_key'] ) && ! isset( $inputs['ignore'] ) ) {
		unset( $inputs['ignore'] );
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.', 'rocket' ), 'updated' );
	}

	return apply_filters( 'rocket_inputs_sanitize', $inputs );
}

/**
 * When our settings are saved: purge, flush, preload!
 *
 * @since 1.0
 *
 * When the White Label Plugin name has changed, redirect on the correct page slug name to avoid a "you dont have permission" false negative annoying page
 * When the settins menu is hidden, redirect on the main settings page to avoid the same thing
 * (Only when a form is sent from our options page )
 *
 * @since 2.1
 */
add_action( 'update_option_' . WP_ROCKET_SLUG, 'rocket_after_save_options', 10, 2 );
function rocket_after_save_options( $oldvalue, $value ) {
	if ( ! ( is_array( $oldvalue ) && is_array( $value ) ) ) {
		return;
	}
	
	// This values do not need to clean the cache domain
	$removed = array( 
		'purge_cron_interval' => true, 
		'purge_cron_unit'     => true, 
		'wl_plugin_name'      => true, 
		'wl_plugin_URI'       => true, 
		'wl_author'           => true, 
		'wl_author_URI'       => true, 
		'wl_description'      => true, 
		'wl_plugin_slug'      => true 
	);

	// Create 2 arrays to compare
	$oldvalue_diff 	= array_diff_key( $oldvalue, $removed );
	$value_diff 	= array_diff_key( $value, $removed );

	// If it's different, clean the domain
	if ( md5( serialize( $oldvalue_diff ) ) !== md5( serialize( $value_diff ) ) ) {
		// Purge all cache files
		rocket_clean_domain();
	}

	// Purge all minify cache files
	if ( ! empty( $_POST ) && ( $oldvalue['minify_css'] != $value['minify_css'] || $oldvalue['exclude_css'] != $value['exclude_css'] ) || ( isset( $oldvalue['cdn'] ) && ! isset( $value['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $value['cdn'] ) ) ) {
		rocket_clean_minify( 'css' );
	}

	if ( ! empty( $_POST ) && ( $oldvalue['minify_js'] != $value['minify_js'] || $oldvalue['exclude_js']  != $value['exclude_js'] ) || ( isset( $oldvalue['cdn'] ) && ! isset( $value['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $value['cdn'] ) ) ) {
		rocket_clean_minify( 'js' );
	}

    // Purge all cache busting files
    if ( ! empty( $_POST ) && ( $oldvalue['remove_query_strings'] != $value['remove_query_strings'] ) ) {
        rocket_clean_cache_busting();
        wp_remote_get(
            home_url(),
            array(
                'timeout'    => 0.01,
                'blocking'   => false,
                'user-agent' => 'wprocketbot',
                'sslverify'  => false
            )
        );
    }

    /*
     * Performs the database optimization when settings are saved with the "save and optimize" submit button"
     */
    if ( ! empty( $_POST ) && isset( $_POST['wp_rocket_settings']['submit_optimize'] ) ) {
        do_rocket_database_optimization();
    }
		
	// Update CloudFlare Development Mode
	$cloudflare_update_result = array();

	if ( ! empty( $_POST ) && isset( $oldvalue['cloudflare_devmode'], $value['cloudflare_devmode'] ) && $oldvalue['cloudflare_devmode'] != $value['cloudflare_devmode'] ) {
        $cloudflare_dev_mode_return = set_rocket_cloudflare_devmode( $value['cloudflare_devmode'] );

        if ( is_wp_error( $cloudflare_dev_mode_return ) ) {
            $cloudflare_update_result[] = array( 'result' => 'error', 'message' => sprintf( __( 'CloudFlare development mode error: %s', 'rocket' ), $cloudflare_dev_mode_return->get_error_message() ) );
        } else {
            $cloudflare_update_result[] = array( 'result' => 'success', 'message' => sprintf( __( 'CloudFlare development mode %s', 'rocket' ), $cloudflare_dev_mode_return ) );
        }
	}
	
	// Update CloudFlare settings
	if ( ! empty( $_POST ) && isset( $oldvalue['cloudflare_auto_settings'], $value['cloudflare_auto_settings'] ) && $oldvalue['cloudflare_auto_settings'] != $value['cloudflare_auto_settings'] ) {
        $cf_old_settings          = explode( ',', $value['cloudflare_old_settings'] );
        // Set Cache Level to Aggressive 
        $cf_cache_level = ( isset( $cf_old_settings[0] ) && $value['cloudflare_auto_settings'] == 0 ) ? $cf_old_settings[0] : 'aggressive';
        $cf_cache_level_return = set_rocket_cloudflare_cache_level( $cf_cache_level );

        if ( is_wp_error( $cf_cache_level_return ) ) {
            $cloudflare_update_result[] = array( 'result' => 'error', 'message' => sprintf( __( 'CloudFlare cache level error: %s', 'rocket' ), $cf_cache_level_return->get_error_message() ) );
        } else {
            $cloudflare_update_result[] = array( 'result' => 'success', 'message' => sprintf( __( 'CloudFlare cache level set to %s', 'rocket' ), $cf_cache_level_return ) );
        }

        // Active Minification for HTML, CSS & JS
        $cf_minify        = ( isset( $cf_old_settings[1] ) && $value['cloudflare_auto_settings'] == 0 ) ? $cf_old_settings[1] : 'on';
        $cf_minify_return = set_rocket_cloudflare_minify( $cf_minify );

        if ( is_wp_error( $cf_minify_return ) ) {
            $cloudflare_update_result[] = array( 'result' => 'error', 'message' => sprintf( __( 'CloudFlare minification error: %s', 'rocket' ), $cf_minify_return->get_error_message() ) );
        } else {
            $cloudflare_update_result[] = array( 'result' => 'success', 'message' => sprintf( __( 'CloudFlare minification %s', 'rocket' ), $cf_minify_return ) );
        }

        // Deactivate Rocket Loader to prevent conflicts
        $cf_rocket_loader = ( isset( $cf_old_settings[2] ) && $value['cloudflare_auto_settings'] == 0 ) ? $cf_old_settings[2] : 'off';
        $cf_rocket_loader_return = set_rocket_cloudflare_rocket_loader( $cf_rocket_loader );

        if ( is_wp_error( $cf_rocket_loader_return ) ) {
            $cloudflare_update_result[] = array( 'result' => 'error', 'message' => sprintf( __( 'CloudFlare rocket loader error: %s', 'rocket' ), $cf_rocket_loader_return->get_error_message() ) );
        } else {
            $cloudflare_update_result[] = array( 'result' => 'success', 'message' => sprintf( __( 'CloudFlare rocket loader %s', 'rocket' ), $cf_rocket_loader_return ) );
        }

        // Set Browser cache to 1 month
        $cf_browser_cache_ttl = ( isset( $cf_old_settings[3] ) && $value['cloudflare_auto_settings'] == 0 ) ? $cf_old_settings[3] : '2678400';
        $cf_browser_cache_return = set_rocket_cloudflare_browser_cache_ttl( $cf_browser_cache_ttl );

        if ( is_wp_error( $cf_browser_cache_return ) ) {
            $cloudflare_update_result[] = array( 'result' => 'error', 'message' => sprintf( __( 'CloudFlare browser cache error: %s', 'rocket' ), $cf_browser_cache_return->get_error_message() ) );
        } else {
            $cloudflare_update_result[] = array( 'result' => 'success', 'message' => sprintf( __( 'CloudFlare browser cache set to %s seconds', 'rocket' ), $cf_browser_cache_return ) );
        }
	}

    if ( ( bool ) $cloudflare_update_result ) {
        set_transient( $GLOBALS['current_user']->ID . '_cloudflare_update_settings', $cloudflare_update_result );
    }
	
	// Regenerate advanced-cache.php file
	if ( ! empty( $_POST ) && ( ( isset( $oldvalue['do_caching_mobile_files'] ) && ! isset( $value['do_caching_mobile_files'] ) ) || ( ! isset( $oldvalue['do_caching_mobile_files'] ) &&  isset( $value['do_caching_mobile_files'] ) ) || ( isset( $oldvalue['do_caching_mobile_files'], $value['do_caching_mobile_files'] ) ) && $oldvalue['do_caching_mobile_files'] != $value['do_caching_mobile_files'] ) ) {
        rocket_generate_advanced_cache_file();
    }
	
	// Update .htaccess file rules
	flush_rocket_htaccess( ! rocket_valid_key() );

	// Update config file
	rocket_generate_config_file();

	// Set WP_CACHE constant in wp-config.php
	if ( ! defined( 'WP_CACHE' ) || ! WP_CACHE ) {
		set_rocket_wp_cache_define( true );
	}
	
	// Redirect on the correct page slug name to avoid false negative error message
	if ( ! empty( $_POST ) && $oldvalue['wl_plugin_name'] != $value['wl_plugin_name'] &&
		isset( $_POST['option_page'], $_POST['action'] ) && 'wp_rocket' == $_POST['option_page'] && 'update' == $_POST['action'] )
	{
		add_settings_error( 'general', 'settings_updated', __( 'Settings saved.', 'rocket' ), 'updated' );
		set_transient( 'settings_errors', get_settings_errors(), 30 );
		wp_redirect( admin_url( 'options-general.php?page=' . sanitize_key( $value['wl_plugin_name'] ) . '&settings-updated=true' ) );
		die();
	}
}

/**
 * Auto-activate the SSL option is the website URL is updated with https protocol
 *
 * @since 2.7
 */
add_action( 'update_option_home', '__rocket_update_ssl_option_after_save_home_url', 10, 2 );
function __rocket_update_ssl_option_after_save_home_url( $oldvalue, $value ) {
	if ( 'https' === parse_url( $value, PHP_URL_SCHEME ) ) {
		update_rocket_option( 'cache_ssl', 1 );
	}
}

/**
 * When purge settings are saved we change the scheduled purge
 *
 * @since 1.0
 */
add_filter( 'pre_update_option_' . WP_ROCKET_SLUG, 'rocket_pre_main_option', 10, 2 );
function rocket_pre_main_option( $newvalue, $oldvalue ) {
	if ( ( isset( $newvalue['purge_cron_interval'], $oldvalue['purge_cron_interval'] ) && $newvalue['purge_cron_interval'] != $oldvalue['purge_cron_interval'] ) || 	( isset( $newvalue['purge_cron_unit'], $oldvalue['purge_cron_unit'] ) && $newvalue['purge_cron_unit'] != $oldvalue['purge_cron_unit'] ) ) {
		// Clear WP Rocket cron
		if ( wp_next_scheduled( 'rocket_purge_time_event' ) ) {
			wp_clear_scheduled_hook( 'rocket_purge_time_event' );
		}
	}

    // Clear WP Rocket database optimize cron if the setting has been modified
    if ( ( isset( $newvalue['schedule_automatic_cleanup'], $oldvalue['schedule_automatic_cleanup'] ) && $newvalue['schedule_automatic_cleanup'] != $oldvalue['schedule_automatic_cleanup'] ) || ( ( isset( $newvalue['automatic_cleanup_frequency'], $oldvalue['automatic_cleanup_frequency'] ) && $newvalue['automatic_cleanup_frequency'] != $oldvalue['automatic_cleanup_frequency'] ) ) ) {
        if ( wp_next_scheduled( 'rocket_database_optimization_time_event' ) ) {
            wp_clear_scheduled_hook( 'rocket_database_optimization_time_event' );
        }
    }

	// Regenerate the minify key if CSS files have been modified
	if ( ( isset( $newvalue['minify_css'], $oldvalue['minify_css'] ) && $newvalue['minify_css'] != $oldvalue['minify_css'] )
		|| ( isset( $newvalue['exclude_css'], $oldvalue['exclude_css'] ) && $newvalue['exclude_css'] != $oldvalue['exclude_css'] )
		|| ( isset( $oldvalue['cdn'] ) && ! isset( $newvalue['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $newvalue['cdn'] ) )
	) {
		$newvalue['minify_css_key'] = create_rocket_uniqid();
	}

	// Regenerate the minify key if JS files have been modified
	if ( ( isset( $newvalue['minify_js'], $oldvalue['minify_js'] ) && $newvalue['minify_js'] != $oldvalue['minify_js'] )
		|| ( isset( $newvalue['exclude_js'], $oldvalue['exclude_js'] ) && $newvalue['exclude_js'] != $oldvalue['exclude_js'] )
		|| ( isset( $newvalue['minify_js_in_footer'], $oldvalue['minify_js_in_footer'] ) && $newvalue['minify_js_in_footer'] != $oldvalue['minify_js_in_footer'] )
		|| ( isset( $oldvalue['cdn'] ) && ! isset( $newvalue['cdn'] ) || ! isset( $oldvalue['cdn'] ) && isset( $newvalue['cdn'] ) )
	) {
		$newvalue['minify_js_key'] = create_rocket_uniqid();
	}

    // Update CloudFlare zone ID if CloudFlare domain was changed
    if ( isset( $newvalue['cloudflare_domain'], $oldvalue['cloudflare_domain'] ) && $newvalue['cloudflare_domain'] != $oldvalue['cloudflare_domain'] && 0 < (int) get_rocket_option( 'do_cloudflare' ) && phpversion() >= '5.4' ) {
        require( WP_ROCKET_ADMIN_PATH . 'compat/cf-options-5.4.php' );
    }

	// Save old CloudFlare settings
	if ( ( isset( $newvalue['cloudflare_auto_settings'], $oldvalue['cloudflare_auto_settings'] ) && $newvalue['cloudflare_auto_settings'] != $oldvalue['cloudflare_auto_settings'] && $newvalue['cloudflare_auto_settings'] == 1 ) && 0 < (int) get_rocket_option( 'do_cloudflare' ) && phpversion() >= '5.4' ) {
		$cf_settings = get_rocket_cloudflare_settings();
        $newvalue['cloudflare_old_settings'] = ( ! is_wp_error( $cf_settings ) ) ? implode( ',', array_filter( $cf_settings ) ) : '';
	}
	
	// Checked the SSL option if the whole website is on SSL
	if( rocket_is_ssl_website() ) {
		$newvalue['cache_ssl'] = 1;
	}
		
	if ( ! defined( 'WP_ROCKET_ADVANCED_CACHE' ) ) {
		rocket_generate_advanced_cache_file();
	}

	if ( $keys = get_transient( WP_ROCKET_SLUG ) ) {
		delete_transient( WP_ROCKET_SLUG );
		$newvalue = array_merge( $newvalue, $keys );
	}

	return $newvalue;
}

/**
 * Outputs the form used by the importers to accept the data to be imported
 *
 * @since 2.2
 */
function rocket_import_upload_form() {
	/**
	 * Filter the maximum allowed upload size for import files.
	 *
	 * @since (WordPress) 2.3.0
	 *
	 * @see wp_max_upload_size()
	 *
	 * @param int $max_upload_size Allowed upload size. Default 1 MB.
	 */
	$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() ); // Filter from WP Core
	$size = size_format( $bytes );
	$upload_dir = wp_upload_dir();
	if ( ! empty( $upload_dir['error'] ) ) {
		?><div class="error"><p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'rocket' ); ?></p>
		<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
	} else {
		?>
		<p>
		<input type="file" id="upload" name="import" size="25" />
		<br />
		<label for="upload"><?php echo apply_filters( 'rocket_help', __( 'Choose a file from your computer:', 'rocket' ) . ' (' . sprintf( __( 'Maximum size: %s', 'rocket' ), $size ) . ')', 'upload', 'help' ); ?></label>
		<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
		</p>
		<?php submit_button( __( 'Upload file and import settings', 'rocket' ), 'button', 'import' );
	}
}

/**
 * Count the number of items concerned by the database cleanup
 *
 * @since 2.8
 * @author Remy Perona
 *
 * @param string $type Item type to count
 * @return int Number of items for this type
 */
function rocket_database_count_cleanup_items( $type ) {
    global $wpdb;

    $count = 0;

    switch( $type ) {
        case 'revisions':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_type = %s", 'revision' ) );
            break;
        case 'auto_drafts':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = %s", 'auto-draft' ) );
            break;
        case 'trashed_posts':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(ID) FROM $wpdb->posts WHERE post_status = %s", 'trash' ) );
            break;
        case 'spam_comments':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE comment_approved = %s", 'spam' ) );
            break;
        case 'trashed_comments':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(comment_ID) FROM $wpdb->comments WHERE (comment_approved = %s OR comment_approved = %s)", 'trash', 'post-trashed' ) );
            break;
        case 'expired_transients':
            $time = isset( $_SERVER['REQUEST_TIME'] ) ? (int) $_SERVER['REQUEST_TIME'] : time();
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_name) FROM $wpdb->options WHERE option_name LIKE %s AND option_value < %d;", '_transient_timeout%', $time ) );
            break;
        case 'all_transients':
            $count = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(option_id) FROM $wpdb->options WHERE option_name LIKE %s", '%_transient_%' ) );
            break;
        case 'optimize_tables':
            $count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(table_name) FROM information_schema.tables WHERE table_schema = %s and Engine <> 'InnoDB' and data_free > 0", DB_NAME ) );
            break;
    }

    return $count;
}
