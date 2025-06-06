<?php
/**
 * Optin section template.
 *
 * @param array $data {
 *    @type int $current_value Current value of the optin option.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-fieldsContainer">
	<fieldset class="wpr-fieldsContainer-fieldset">
		<div class="wpr-field wpr-field--radio">
			<div class="wpr-radio">
				<input type="checkbox" id="analytics_enabled" class="" name="rocket_mixpanel_optin" value="1" <?php checked( $data['current_value'], 1 ); ?>>
				<label for="analytics_enabled" class="">
					<span data-l10n-active="On"
						data-l10n-inactive="Off" class="wpr-radio-ui"></span>
					<?php esc_html_e( 'Rocket Analytics', 'rocket' ); ?>
				</label>
			</div>
			<div class="wpr-field-description">
				<?php
				// translators: %1$s = opening <a> tag, %2$s = closing </a> tag.
				printf( esc_html__( 'I agree to share anonymous data with the development team to help improve WP Rocket. %1$sWhat info will we collect?%2$s', 'rocket' ), '<button class="wpr-js-popin">', '</button>' );
				?>
			</div>
		</div>
	</fieldset>
</div>
