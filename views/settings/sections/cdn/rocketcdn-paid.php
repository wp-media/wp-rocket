<?php
/**
 * RocketCDN Paid (Pro) section template.
 *
 * Displays the status indicator for the paid CDN plan.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Section data.
 *
 *     @type string $id          Section identifier.
 *     @type string $title       Section title.
 *     @type string $description Section description.
 *     @type string $class       Section classes.
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 *     @type array  $status_indicator Data for the CDN status indicator partial.
 *     @type bool   $is_active    Whether RocketCDN Pro is the currently applied CDN mode.
 *     @type bool   $is_forced_off Whether the mode toggle must be disabled (subscription loading, expired/banned licence).
 *     @type string $toggle_tooltip Tooltip shown on the mode toggle when $is_forced_off is true.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_rocketcdn_paid_active = $data['is_active'];
?>

<?php // wpr-cdn-active-indicator is kept in sync with JS (cdn-driver.js), which reads it off .wpr-optionHeader to find the previously-active mode on a failed switch — it no longer drives any visible styling on this header itself. ?>
<div class="wpr-optionHeader wpr-optionHeader--cdn-driver <?php echo esc_attr( $data['class'] ); ?><?php echo $rocket_rocketcdn_paid_active ? ' wpr-cdn-active-indicator' : ''; ?>">
	<div class="wpr-optionHeader__title-group">
		<h3 class="wpr-title2 wpr-title2--orange"><?php echo esc_html( $data['title'] ); ?></h3>
		<span class="wpr-badge wpr-badge--blue"><?php esc_html_e( 'Pro', 'rocket' ); ?></span>
		<?php
		$this->render_parts_with_data(
			'cdn/cdn-mode-toggle',
			[
				'id'            => 'wpr-rocketcdn-paid-toggle',
				'cdn_mode'      => 'rocketcdn_paid',
				'checked'       => $rocket_rocketcdn_paid_active,
				'is_forced_off' => $data['is_forced_off'],
				'label'         => __( 'Enable RocketCDN Paid', 'rocket' ),
				'tooltip'       => $data['toggle_tooltip'],
			]
		);
		?>
	</div>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Settings" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<?php
$this->render_parts_with_data( 'cdn/cdn-status-indicator', $data['status_indicator'] );
?>
