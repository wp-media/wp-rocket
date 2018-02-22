<?php
/**
 * Cloudflare section template.
 *
 * @since 3.0
 *
 * @param array {
 *     Section arguments.
 *
 *     @type string $id    Page section identifier.
 *     @type string $title Page section title.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<h2 id="<?php echo esc_attr( $data['id'] ); ?>"><?php echo esc_html( $data['title'] ); ?></h2>
<?php $this->render_settings_sections( $data['id'] ); ?>
<h3><?php esc_html_e( 'Cloudflare Cache', 'rocket' ); ?></h3>
<p><?php esc_html_e( 'Purges cached resources for your website. Learn more', 'rocket' ); ?></p>
