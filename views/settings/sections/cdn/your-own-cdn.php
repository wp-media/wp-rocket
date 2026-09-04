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
 *     @type bool   $is_active   Whether BYOCDN is the currently applied CDN mode.
 *     @type bool   $is_forced_off Whether the mode toggle must be disabled (e.g. a hosting compatibility layer manages CDN itself).
 *     @type bool   $show_no_cname_warning Whether to display the missing-CNAME warning (BYOCDN active, no CNAME configured).
 * }
 */

defined( 'ABSPATH' ) || exit;
$rocket_byocdn_active = $data['is_active'];
?>

<div class="wpr-optionHeader <?php echo esc_attr( $data['class'] ); ?><?php echo $rocket_byocdn_active ? ' wpr-cdn-active-indicator' : ''; ?>">
	<label class="wpr-cdn-mode-toggle">
		<input
			type="checkbox"
			class="wpr-cdn-mode-toggle__input"
			id="wpr-byocdn-toggle"
			data-cdn-mode="byocdn"
			<?php checked( $rocket_byocdn_active ); ?>
			<?php disabled( $data['is_forced_off'] ); ?>
		/>
		<span class="wpr-cdn-mode-toggle__slider"></span>
	</label>
	<div class="wpr-optionHeader__title-group">
		<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
		<span class="wpr-cdn-active-label"><?php esc_html_e( 'Active', 'rocket' ); ?></span>
	</div>
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
	<?php if ( ! empty( $data['show_no_cname_warning'] ) ) : ?>
	<div class="wpr-notice wpr-ri-notice wpr-cdn-no-cname-warning">
		<div class="wpr-notice-container">
			<div class="wpr-notice-description wpr-notice-70">
				<p><?php esc_html_e( 'Other CDN is active, but no CNAME has been configured yet — your assets will not be delivered through a CDN until you add one.', 'rocket' ); ?></p>
			</div>
			<a
				href="#"
				class="wpr-notice-close wpr-cdn-no-cname-warning__cta"
			>
				<?php esc_html_e( 'Use RocketCDN Free instead', 'rocket' ); ?>
			</a>
		</div>
	</div>
	<?php endif; ?>
	<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
</div>
