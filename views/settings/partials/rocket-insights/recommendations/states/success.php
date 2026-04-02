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
		echo esc_html__( 'All done!', 'rocket' );
		?>
	</p>
	<p class="wpr-recommendations__success-message">
		<?php esc_html_e( 'All recommended WP Rocket features are now enabled.', 'rocket' ); ?>
	</p>
</div>
