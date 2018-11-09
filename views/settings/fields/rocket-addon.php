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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$settings_page = ! empty( $data['settings_page'] ) ? $data['settings_page'] : '';
?>

<fieldset class="wpr-fieldsContainer-fieldset">
	<div class="wpr-field">
		<div class="wpr-flex">
			<h4 class="wpr-title3"><?php echo esc_html( $data['label'] ); ?></h4>
			<div class="wpr-radio wpr-radio--reverse">
				<input type="checkbox" id="<?php echo esc_attr( $data['id'] ); ?>" class="" name="wp_rocket_settings[<?php echo esc_attr( $data['id'] ); ?>]" value="1" <?php checked( $data['value'], 1 ); ?>>
				<label for="<?php echo esc_attr( $data['id'] ); ?>" class="">
					<span data-l10n-active="<?php echo esc_attr_x( 'On', 'Active state of checkbox', 'rocket' ); ?>"
  data-l10n-inactive="<?php echo esc_attr_x( 'Off', 'Inactive state of checkbox', 'rocket' ); ?>" class="wpr-radio-ui"></span>
					<?php esc_html_e( 'Add-on status', 'rocket' ); ?>
				</label>
			</div>
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
						<?php echo $data['title']; ?>
					</div>
				<?php endif; ?>
				<?php if ( ! empty( $data['description'] ) ) : ?>
					<div class="wpr-field-description">
						<?php echo $data['description']; ?>
					</div>
				<?php endif; ?>
				<?php if ( $settings_page ) : ?>
					<a href="#<?php echo esc_attr( $settings_page ); ?>" class="wpr-button wpr-button--small wpr-button--icon wpr-button--purple wpr-icon-chevron-right wpr-toggle-button wpr-<?php echo esc_attr( $settings_page ); ?>ToggleButton"><?php esc_html_e( 'Modify options', 'rocket' ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</fieldset>
