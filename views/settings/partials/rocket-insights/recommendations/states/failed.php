<?php
/**
 * Recommendations Failed State template.
 *
 * Shown when recommendations fetch failed.
 *
 * @since 3.21
 *
 * @param array $data {
 *     Data for failed state.
 *
 *     @type string $error_title   Optional error title to display.
 *     @type string $error_message Optional error message to display.
 * }
 */

defined( 'ABSPATH' ) || exit;

$error_title   = __( 'We’re sorry, recommendations are currently unavailable.', 'rocket' );
$error_message = __( 'Please check the documentation for next steps', 'rocket' );
?>
<div class="wpr-recommendations__failed">
	<p class="wpr-recommendations__failed-title">
		<?php echo esc_html( $error_title ); ?>
	</p>
	<p class="wpr-recommendations__failed-message">
		<?php echo esc_html( $error_message ); ?>
	</p>
	<a href="#" 
	   target="_blank" 
	   rel="noopener noreferrer" 
	   class="wpr-recommendations__failed-link">
		<?php esc_html_e( 'Read documentation', 'rocket' ); ?>
		<span class="wpr-icon-external-link"></span>
	</a>
</div>
