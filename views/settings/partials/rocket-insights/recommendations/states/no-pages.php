<?php
/**
 * Recommendations No Pages State template.
 *
 * Shown when no page is tracked in Rocket Insights yet.
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-recommendations__no-pages">
	<p class="wpr-recommendations__no-pages-title">
		<?php esc_html_e( 'Add a first page to get performance recommendations.', 'rocket' ); ?>
	</p>
</div>
