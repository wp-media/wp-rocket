<?php
/**
 * Import page section template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<h2 id="import>"><?php esc_html_e( 'Import/Export', 'rocket' ); ?></h2>
<?php
$this->render_import_form();
