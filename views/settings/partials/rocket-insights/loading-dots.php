<?php
/**
 * Loading dots.
 *
 * Same loader as orange-loading.svg, animated with CSS because browsers stop SMIL animations inside an <img> after a few seconds.
 */

defined( 'ABSPATH' ) || exit;
?>
<svg class="wpr-loading-dots" viewBox="0 0 100 100" aria-hidden="true" focusable="false">
	<?php for ( $rocket_dot = 0; $rocket_dot < 12; $rocket_dot++ ) : ?>
		<circle cx="<?php echo esc_attr( round( 50 + 40 * cos( deg2rad( $rocket_dot * 30 ) ), 3 ) ); ?>" cy="<?php echo esc_attr( round( 50 + 40 * sin( deg2rad( $rocket_dot * 30 ) ), 3 ) ); ?>" r="6" style="--wpr-dot: <?php echo esc_attr( $rocket_dot ); ?>"></circle>
	<?php endfor; ?>
</svg>
