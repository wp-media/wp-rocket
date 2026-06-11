<?php
/**
 * CDN driver container section template.
 *
 * Generic container for all CDN drivers (Built-in CDN, RocketCDN Unlimited, BYOCDN).
 * Includes the CDN status indicator and PAUSE CDN button.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Fields container data.
 *
 *     @type string $id          Section identifier.
 *     @type string $title       Section title.
 *     @type string $description Section description.
 *     @type string $class       Section classes.
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader <?php echo esc_attr( $data['class'] ); ?>">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Settings" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<div class="wpr-fieldsContainer-fieldset <?php echo esc_attr( $data['class'] ); ?>">
	<?php
	$this->render_parts_with_data( 'cdn/cdn-status-indicator', $data['status_indicator'] );
	?>

	<?php if ( $data['status_indicator']['is_active'] ) : ?>
	<div class="wpr-cdn-built-in__separator"></div>
	<?php endif; ?>

	<?php if ( ! empty( $data['description'] ) ) : ?>
	<div class="wpr-fieldsContainer-description">
		<?php echo $data['description']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
	</div>
	<?php endif; ?>
	<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
</div>
