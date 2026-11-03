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
 * }
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
	<?php if ( ! empty( $data['help'] ) ) : ?>
	<button data-wap-prompt="<?php echo esc_attr( sprintf( /* translators: 1: settings tab name, 2: settings section name */ __( "I need help with '%1\$s' > '%2\$s'", 'rocket' ), $data['page_title'], $data['title'] ) ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Addons" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></button>
	<?php endif; ?>
</div>

<div class="wpr-fieldsContainer">
	<div class="wpr-fieldsContainer-description">
		<?php echo esc_html( $data['description'] ); ?>
	</div>
	<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
</div>
