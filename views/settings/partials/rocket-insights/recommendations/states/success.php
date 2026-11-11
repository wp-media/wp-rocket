<?php
/**
 * Recommendations Success State template.
 *
 * Shown when all recommendations have been applied (no recommendations returned).
 * Shared by the dashboard and the sidebar widget.
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-recommendations__success">
	<div class="wpr-recommendations__success-content">
		<p class="wpr-recommendations__success-title">
			<?php esc_html_e( 'Your website is doing great! 🥳', 'rocket' ); ?>
		</p>
		<p class="wpr-recommendations__success-message">
			<?php esc_html_e( 'All recommended settings are enabled! For more advanced recommendations, you can:', 'rocket' ); ?>
		</p>
	</div>
	<ul class="wpr-recommendations__success-links">
		<li>
			<a href="#rocket_insights" class="wpr-recommendations__success-link">
				<?php esc_html_e( 'Explore GTmetrix reports', 'rocket' ); ?>
			</a>
		</li>
		<li>
			<a href="<?php echo esc_url( 'https://docs.wp-rocket.me/article/1876-rocket-insights?utm_source=wp_plugin&utm_medium=wp_rocket' ); ?>" class="wpr-recommendations__success-link" target="_blank" rel="noopener noreferrer">
				<?php esc_html_e( 'Read our documentation', 'rocket' ); ?>
			</a>
		</li>
	</ul>
</div>
