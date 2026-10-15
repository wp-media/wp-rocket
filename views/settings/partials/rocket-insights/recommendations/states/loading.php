<?php
/**
 * Recommendations Loading State template.
 *
 * Shown when recommendations are being fetched.
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-recommendations__loading">
	<div class="wpr-recommendations__loading-spinner">
		<?php // Same loader as orange-loading.svg, animated with CSS because browsers stop SMIL animations inside an <img> after a few seconds. ?>
		<svg class="wpr-loading-dots" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
			<?php for ( $rocket_dot = 0; $rocket_dot < 12; $rocket_dot++ ) : ?>
				<circle cx="<?php echo esc_attr( round( 50 + 40 * cos( deg2rad( $rocket_dot * 30 ) ), 3 ) ); ?>" cy="<?php echo esc_attr( round( 50 + 40 * sin( deg2rad( $rocket_dot * 30 ) ), 3 ) ); ?>" r="6" style="--wpr-dot: <?php echo esc_attr( $rocket_dot ); ?>"></circle>
			<?php endfor; ?>
		</svg>
	</div>
	<p class="wpr-recommendations__loading-text">
		<?php esc_html_e( 'Loading recommendations...', 'rocket' ); ?>
		<br />
		<?php esc_html_e( 'Almost there!', 'rocket' ); ?>
	</p>
</div>
