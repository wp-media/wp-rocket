<?php
/**
 * Promo banner.
 *
 * @since 3.7.3
 */

defined( 'ABSPATH' ) || exit;
?>
<div>
	<section>
		<span>
			<?php
			// translators: %s = promotion discount percentage.
			printf( esc_html__( '%s% off', 'rocket' ), esc_html( $data['promo_discount'] ) );
			?>
		</span>
		<h3>
			<?php
			// translators: %s = promotion name.
			printf( esc_html__( '%s promotion is live!', 'rocket' ), esc_html( $data['promo_name'] ) );
			?>
		</h3>
		<p><?php echo esc_html( $data['message'] ); ?></p>
	<section>
	<div>
		<p><?php esc_html_e( 'Hurry Up! Deal ends in:', 'rocket' ); ?></p>
		<div></div>
		<button><?php esc_html_e( 'Upgrade now', 'rocket' ); ?></button>
	</div>
</div>
