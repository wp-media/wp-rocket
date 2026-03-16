<?php
/**
 * Recommendations Success State template.
 *
 * Shown when all recommendations have been applied (no recommendations returned).
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-recommendations__success">
	<p class="wpr-recommendations__success-title">
		<?php
		echo esc_html__( 'Your website is doing great!', 'rocket' );
		?>🥳
	</p>
	<p class="wpr-recommendations__success-message">
		<?php esc_html_e( 'For more advanced recommendations, refer to the full report above.', 'rocket' ); ?>
	</p>
</div>
