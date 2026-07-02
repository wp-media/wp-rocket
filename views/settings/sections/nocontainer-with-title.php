<?php
/**
 * Settings fields with no container template.
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
 *     @type string $helper      Section helper text.
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>


<div class="wpr-optionHeader <?php echo esc_attr( $data['class'] ); ?>">
	<h3 class="wpr-title2"><?php echo $data['title']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?></h3>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>"
		data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>"
		<?php if ( ! empty( $data['help']['rocketcdn_url'] ) ) : ?>
		data-rocketcdn-url="<?php echo esc_url( $data['help']['rocketcdn_url'] ); ?>"
		data-rocketcdn-id="<?php echo esc_attr( $data['help']['rocketcdn_id'] ); ?>"
		data-other-cdn-url="<?php echo esc_url( $data['help']['other_cdn_url'] ); ?>"
		data-other-cdn-id="<?php echo esc_attr( $data['help']['other_cdn_id'] ); ?>"
		<?php endif; ?>
		data-wpr_track_button="Need Help"
		data-wpr_track_context="Settings"
		class="wpr-infoAction wpr-infoAction--help wpr-icon-help<?php echo ! empty( $data['help']['rocketcdn_url'] ) ? ' exclude-cdn-help-js' : ''; ?>"
		target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php endif; ?>
</div>

<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>