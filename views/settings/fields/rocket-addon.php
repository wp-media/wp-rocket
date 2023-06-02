<?php
/**
 * Rocket add-on block template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Checkbox Field arguments.
 *
 *     @type string $id          Field identifier.
 *     @type string $label       Add-on label.
 *     @type string $title       Add-on title.
 *     @type string $description Add-on description.
 *     @type string $logo        Add-on logo.
 *     @type string $value       Field value.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_settings_page = ! empty( $data['settings_page'] ) ? $data['settings_page'] : '';
?>

<fieldset class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<div class="wpr-flex">
			<h4 class="wpr-title3"><?php echo esc_html( $data['label'] ); ?></h4>
			<?php
			$rocket_default = true;
			// This filter is documented in one-click-addon.php.
			$rocket_display = apply_filters( 'rocket_display_input_' . $data['id'], $rocket_default );

			if ( ! is_bool( $rocket_display ) ) {
				$rocket_display = $rocket_default;
			}

			if ( $rocket_display ) :
				?>
				<div class="wpr-radio wpr-radio--reverse">
					<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?> <?php echo $data['input_attr'];//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>>
					<label for="<?php echo esc_attr( $data['id'] ); ?>" class="">
						<span data-l10n-active="On" data-l10n-inactive="Off" class="wpr-radio-ui"></span>
						<?php esc_html_e( 'Add-on status', 'rocket' ); ?>
					</label>
				</div>
			<?php endif; ?>
		</div>
	</div>

	<div class="wpr-field wpr-addon">
		<div class="wpr-flex">
			<div class="wpr-addon-logo">
				<img src="<?php echo esc_url( $data['logo']['url'] ); ?>" width="<?php echo esc_attr( $data['logo']['width'] ); ?>" height="<?php echo esc_attr( $data['logo']['height'] ); ?>" alt="">
			</div>
			<div class="wpr-addon-text">
				<?php if ( ! empty( $data['title'] ) ) : ?>
					<div class="wpr-addon-title">
						<?php echo esc_attr( $data['title'] ); ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $data['description'] ) ) : ?>
					<div class="wpr-field-description">
						<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $data['helper'] ) ) : ?>
					<div class="wpr-field-helper">
						<?php echo $data['helper']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
					</div>
				<?php endif; ?>
				<?php if ( $rocket_settings_page ) : ?>
					<a href="#<?php echo esc_attr( $rocket_settings_page ); ?>" class="wpr-button wpr-button--small wpr-button--icon wpr-button--purple wpr-icon-chevron-right wpr-toggle-button wpr-<?php echo esc_attr( $rocket_settings_page ); ?>ToggleButton"><?php esc_html_e( 'Modify options', 'rocket' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</fieldset>
