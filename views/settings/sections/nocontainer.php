<?php
/**
 * Settings fields wrapper template.
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

defined( 'ABSPATH' ) || exit;

?>
<?php echo esc_html( $data['title'] ); ?>
<?php
$this->render_settings_fields( $data['page'], $data['id'] );
