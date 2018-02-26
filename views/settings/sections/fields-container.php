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
 *     @type string $help        Data to pass to beacon.
 *     @type string $page        Page section identifier.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>


<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['title'] ); ?></h3>
    <button data-beacon-id="<?php echo esc_attr( $data['help'] ); ?>" class="wpr-infoAction wpr-infoAction--help wpr-icon-help"><?php _e( 'Need Help?', 'rocket' ); ?></button>
</div>

<?php echo esc_html( $data['description'] ); ?>
<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
