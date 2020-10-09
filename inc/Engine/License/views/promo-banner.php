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
		<p>
			<?php
			// translators: %1$s = promotion name, %2$s = promotion discount percentage.
			printf( esc_html__( 'Take advantage of %1$s to speed up more websites: get a %2$s% off for upgrading your license to Plus or Infinite!', 'rocket' ), esc_html( $data['promo_name'] ), esc_html( $data['promo_discount'] ) );
			?>
		</p>
	<section>
	<div>
		<p><?php esc_html_e( 'Hurry Up! Deal ends in:', 'rocket' ); ?></p>
		<div></div>
		<button><?php esc_html_e( 'Upgrade now', 'rocket' ); ?></button>
	</div>
</div>
