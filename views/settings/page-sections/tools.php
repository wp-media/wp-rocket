<?php
/**
 * Import page section template.
 *
 * @since 3.0
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>

<div id="tools" class="wpr-page">
	<div class="wpr-sectionHeader">
		<h2 class="wpr-title1 wpr-icon-tools"><?php esc_html_e( 'Import/Export', 'rocket' ); ?></h2>
	</div>
	<?php $this->render_import_form(); ?>
</div>
