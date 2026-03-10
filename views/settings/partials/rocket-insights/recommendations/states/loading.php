<?php
/**
 * Recommendations Loading State template.
 *
 * Shown when recommendations are being fetched.
 *
 * @since 3.21
 */

defined( 'ABSPATH' ) || exit;

$rocket_img_url = esc_url( WP_ROCKET_ASSETS_IMG_URL );
?>
<div class="wpr-recommendations__loading">
	<div class="wpr-recommendations__loading-spinner"></div>
	<p class="wpr-recommendations__loading-text">
		<?php esc_html_e( 'Loading recommendations...', 'rocket' ); ?>
        <br />
        <?php esc_html_e( 'Almost there!', 'rocket' ); ?>
	</p>
</div>
