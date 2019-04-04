<?php
/**
 * CNAMES template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$cnames      = get_rocket_option( 'cdn_cnames' );
$cnames_zone = get_rocket_option( 'cdn_zone' );
?>
<div class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<div class="wpr-field-description-label">
			<?php echo $data['label']; ?>
		</div>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<div class="wpr-field-description">
				<?php echo $data['description']; ?>
			</div>
		<?php endif; ?>
		<div id="wpr-cnames-list">
		<?php
		if ( $cnames ) :
			foreach ( $cnames as $key => $url ) :
				?>
					<div class="wpr-multiple">
						<div class="wpr-text">
							<input type="text" name="wp_rocket_settings[cdn_cnames][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $url ); ?>" placeholder="cdn.example.com" />
						</div>
						<div class="wpr-field-betweenText"><?php _e( 'reserved for', 'rocket' ); ?></div>
						<div class="wpr-select">
							<select name="wp_rocket_settings[cdn_zone][<?php echo esc_attr( $key ); ?>]">
								<option value="all" <?php selected( $cnames_zone[ $key ], 'all' ); ?>><?php esc_html_e( 'All files', 'rocket' ); ?></option>
								<?php
								/**
								 * Controls the inclusion of images option for the CDN dropdown
								 *
								 * @since 2.10.7
								 * @author Remy Perona
								 *
								 * @param bool $allow true to add the option, false otherwise.
								 */
								if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
									?>
									<option value="images" <?php selected( $cnames_zone[ $key ], 'images' ); ?>><?php esc_html_e( 'Images', 'rocket' ); ?></option>
								<?php endif; ?>
								<option value="css_and_js" <?php selected( $cnames_zone[ $key ], 'css_and_js' ); ?>><?php esc_html_e( 'CSS & JavaScript', 'rocket' ); ?></option>
								<option value="js" <?php selected( $cnames_zone[ $key ], 'js' ); ?>><?php esc_html_e( 'JavaScript', 'rocket' ); ?></option>
								<option value="css" <?php selected( $cnames_zone[ $key ], 'css' ); ?>><?php esc_html_e( 'CSS', 'rocket' ); ?></option>
							</select>
						</div>
						<button class="dashicons dashicons-no wpr-multiple-close hide-if-no-js"></button>
					</div>
				<?php
			endforeach;
		else : ?>
			<div class="wpr-multiple wpr-multiple-default">
				<div class="wpr-text">
					<input type="text" name="wp_rocket_settings[cdn_cnames][]" placeholder="cdn.example.com" />
				</div>
				<div class="wpr-field-betweenText"><?php _e( 'reserved for', 'rocket' ); ?></div>
				<div class="wpr-select">
					<select name="wp_rocket_settings[cdn_zone][]">
						<option value="all"><?php esc_html_e( 'All files', 'rocket' ); ?></option>
						<?php
						/**
						 * Controls the inclusion of images option for the CDN dropdown
						 *
						 * @since 2.10.7
						 * @author Remy Perona
						 *
						 * @param bool $allow true to add the option, false otherwise.
						 */
						if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
						?>
						<option value="images"><?php esc_html_e( 'Images', 'rocket' ); ?></option>
						<?php endif; ?>
						<option value="css_and_js"><?php esc_html_e( 'CSS & JavaScript', 'rocket' ); ?></option>
						<option value="js"><?php esc_html_e( 'JavaScript', 'rocket' ); ?></option>
						<option value="css"><?php esc_html_e( 'CSS', 'rocket' ); ?></option>
					</select>
				</div>
			</div>
		<?php endif; ?>
		</div>
		<div id="wpr-cname-model" class="wpr-isHidden">
			<div class="wpr-multiple">
				<div class="wpr-text">
					<input type="text" name="wp_rocket_settings[cdn_cnames][]" placeholder="cdn.example.com" />
				</div>
				<div class="wpr-field-betweenText"><?php _e( 'reserved for', 'rocket' ); ?></div>
				<div class="wpr-select">
					<select name="wp_rocket_settings[cdn_zone][]">
						<option value="all"><?php esc_html_e( 'All files', 'rocket' ); ?></option>
						<?php
						/**
						 * Controls the inclusion of images option for the CDN dropdown
						 *
						 * @since 2.10.7
						 * @author Remy Perona
						 *
						 * @param bool $allow true to add the option, false otherwise.
						 */
						if ( apply_filters( 'rocket_allow_cdn_images', true ) ) :
						?>
						<option value="images"><?php esc_html_e( 'Images', 'rocket' ); ?></option>
						<?php endif; ?>
						<option value="css_and_js"><?php esc_html_e( 'CSS & JavaScript', 'rocket' ); ?></option>
						<option value="js"><?php esc_html_e( 'JavaScript', 'rocket' ); ?></option>
						<option value="css"><?php esc_html_e( 'CSS', 'rocket' ); ?></option>
					</select>
				</div>
				<button class="dashicons dashicons-no wpr-multiple-close hide-if-no-js"></button>
			</div>
		</div>
		<button class='wpr-button wpr-button--small wpr-button--purple wpr-button--icon wpr-icon-plus wpr-button--addMulti'>
			<?php esc_html_e( 'Add CNAME', 'rocket' ); ?>
		</button>
	</div>
</div>