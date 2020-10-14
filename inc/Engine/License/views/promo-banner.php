<?php
/**
 * Promo banner.
 *
 * @since 3.7.4
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="rocket-promo-banner">
	<section>
		<span>
			<?php
			// translators: %s = promotion discount percentage.
			printf( esc_html__( '%s off', 'rocket' ), esc_html( $data['discount_percent'] . '%' ) );
			?>
		</span>
		<h3>
			<?php
			// translators: %s = promotion name.
			printf( esc_html__( '%s promotion is live!', 'rocket' ), esc_html( $data['name'] ) );
			?>
		</h3>
		<p><?php echo esc_html( $data['message'] ); ?></p>
	<section>
	<div>
		<p><?php esc_html_e( 'Hurry Up! Deal ends in:', 'rocket' ); ?></p>
		<div></div>
		<button><?php esc_html_e( 'Upgrade now', 'rocket' ); ?></button>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-promotion"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?></span></button>
</div>
