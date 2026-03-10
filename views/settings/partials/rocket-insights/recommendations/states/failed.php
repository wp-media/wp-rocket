<?php
/**
 * Recommendations Failed State template.
 *
 * Shown when recommendations fetch failed.
 *
 * @since 3.21
 *
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-recommendations__failed">
	<p class="wpr-recommendations__failed-title">
		<?php esc_html_e( 'We’re sorry, recommendations are currently unavailable.', 'rocket' ); ?>
	</p>
	<p class="wpr-recommendations__failed-message">
		<?php esc_html_e( 'Please check the documentation for next steps', 'rocket' ); ?>
	</p>
	<a href="#" 
		target="_blank" 
		rel="noopener noreferrer" 
		class="wpr-recommendations__failed-link">
		<?php esc_html_e( 'Read documentation', 'rocket' ); ?>
		<span class="wpr-icon-external-link"></span>
	</a>
</div>
