<?php
/**
 * CNAMES template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || exit;

$rocket_cnames      = get_rocket_option( 'cdn_cnames' );
$rocket_cnames_zone = get_rocket_option( 'cdn_zone' );

/**
 * Filters the fields to be disabled for the CDN section.
 *
 * @since  3.12.1
 *
 * @param bool $alter Input should be altered.
 */
$rocket_disable_input_alt = apply_filters( 'rocket_disable_cdn_option_change', false );
?>
<div class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field <?php echo $rocket_disable_input_alt ? 'wpr-isDisabled' : ''; ?>">
		<div class="wpr-field-description-label">
			<?php echo $data['label']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
		</div>
		<?php if ( ! empty( $data['description'] ) ) : ?>
			<div class="wpr-field-description">
				<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</div>
		<?php endif; ?>
		<div id="wpr-cnames-list">
		<?php
		if ( $rocket_cnames ) :
			foreach ( $rocket_cnames as $key => $url ) : // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
				?>
					<div class="wpr-multiple">
						<div class="wpr-text">
							<input type="text" name="wp_rocket_settings[cdn_cnames][<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $url ); ?>" placeholder="cdn.example.com" <?php echo $rocket_disable_input_alt ? 'disabled' : ''; ?> />
						</div>
						<div class="wpr-field-betweenText"><?php esc_html_e( 'reserved for', 'rocket' ); ?></div>
						<div class="wpr-select">
							<select name="wp_rocket_settings[cdn_zone][<?php echo esc_attr( $key ); ?>]" <?php echo $rocket_disable_input_alt ? 'disabled' : ''; ?> >
								<option value="all" <?php selected( $rocket_cnames_zone[ $key ], 'all' ); ?>><?php esc_html_e( 'All files', 'rocket' ); ?></option>
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
									<option value="images" <?php selected( $rocket_cnames_zone[ $key ], 'images' ); ?>><?php esc_html_e( 'Images', 'rocket' ); ?></option>
								<?php endif; ?>
								<option value="css_and_js" <?php selected( $rocket_cnames_zone[ $key ], 'css_and_js' ); ?>><?php esc_html_e( 'CSS & JavaScript', 'rocket' ); ?></option>
								<option value="js" <?php selected( $rocket_cnames_zone[ $key ], 'js' ); ?>><?php esc_html_e( 'JavaScript', 'rocket' ); ?></option>
								<option value="css" <?php selected( $rocket_cnames_zone[ $key ], 'css' ); ?>><?php esc_html_e( 'CSS', 'rocket' ); ?></option>
							</select>
						</div>
						<?php if ( ! $rocket_disable_input_alt ) : ?>
							<button class="dashicons dashicons-no wpr-multiple-close hide-if-no-js"></button>
						<?php endif ?>
					</div>
				<?php
			endforeach;
		else :
			?>
			<div class="wpr-multiple wpr-multiple-default">
				<div class="wpr-text">
					<input type="text" name="wp_rocket_settings[cdn_cnames][]" placeholder="cdn.example.com" />
				</div>
				<div class="wpr-field-betweenText"><?php esc_html_e( 'reserved for', 'rocket' ); ?></div>
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
				<div class="wpr-field-betweenText"><?php esc_html_e( 'reserved for', 'rocket' ); ?></div>
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
		<?php if ( ! $rocket_disable_input_alt ) : ?>
			<button class='wpr-button wpr-button--small wpr-button--purple wpr-button--icon wpr-icon-plus wpr-button--addMulti'>
				<?php esc_html_e( 'Add CNAME', 'rocket' ); ?>
			</button>
		<?php endif ?>
	</div>
</div>
