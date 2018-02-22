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

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<h3><?php echo esc_html( $data['title'] ); ?></h3>
<?php echo esc_html( $data['description'] ); ?>
<?php $this->render_settings_fields( $data['page'], $data['id'] ); ?>
