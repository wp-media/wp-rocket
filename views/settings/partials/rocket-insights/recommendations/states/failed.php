<?php
/**
 * Recommendations Failed State template.
 *
 * Shown when recommendations fetch failed.
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-recommendations__failed">
	<p class="wpr-recommendations__failed-title">
		<?php esc_html_e( 'We’re sorry, recommendations are currently unavailable.', 'rocket' ); ?>
	</p>
</div>
