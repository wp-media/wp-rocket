<?php
/**
 * Settings fields container template.
 *
 * @since 3.0
 *
 * @param array $data {
 *     Fields container data.
 *
 *     @type string $id          Section identifier.
 *     @type string $title       Section title.
 *     @type string $description Section description.
 *     @type string $page        Page section identifier.
 *     @type string|array $help  Optional. Beacon article IDs (string), or an array with the
 *                               article `id` and docs `url` (renders a link).
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
	<?php if ( ! empty( $data['help'] ) && is_array( $data['help'] ) ) : ?>
	<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Settings" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
	<?php elseif ( ! empty( $data['help'] ) ) : ?>
	<button data-beacon-id="<?php echo esc_attr( $data['help'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
	<?php endif; ?>
</div>

<div class="wpr-fieldsContainer">
	<div class="wpr-fieldsContainer-description">
		<?php echo esc_html( $data['description'] ); ?>
	</div>
	<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
</div>
