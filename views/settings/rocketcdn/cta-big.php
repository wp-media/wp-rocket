<?php
/**
 * RocketCDN small CTA template.
 *
 * @since 3.5
 *
 * @param array $data {
 *      @type string $container_class container CSS class.
 *      @type string $promotion_campaign Promotion campaign title.
 *      @type string $promotion_end_date Promotion end date.
 *      @type string $nopromo_variant CSS modifier for the no promotion display.
 *      @type string $regular_price RocketCDN regular price.
 *      @type string $current_price RocketCDN current price.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="wpr-rocketcdn-cta <?php echo esc_attr( $data['container_class'] ); ?>" id="wpr-rocketcdn-cta">
	<?php if ( ! empty( $data['promotion_campaign'] ) ) : ?>
	<div class="wpr-flex wpr-rocketcdn-promo">
		<h3 class="wpr-title1"><?php echo esc_html( $data['promotion_campaign'] ); ?></h3>
		<p class="wpr-title2 wpr-rocketcdn-promo-date">
			<?php
			printf(
				// Translators: %s = date formatted using date_i18n() and get_option( 'date_format' ).
				esc_html__( 'Valid until %s only!', 'rocket' ),
				esc_html( $data['promotion_end_date'] )
			);
			?>
		</p>
	</div>
	<?php endif; ?>
	<section class="wpr-rocketcdn-cta-content<?php echo esc_attr( $data['nopromo_variant'] ); ?>">
		<h3 class="wpr-title2">Rocket CDN</h3>
		<p class="wpr-rocketcdn-cta-subtitle"><?php esc_html_e( 'Speed up your website thanks to:', 'rocket' ); ?></p>
		<div class="wpr-flex">
			<ul class="wpr-rocketcdn-features">
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
					<?php
					// translators: %1$s = opening strong tag, %2$s = closing strong tag.
					printf( esc_html__( 'High performance Content Delivery Network (CDN) with %1$sunlimited bandwith%2$s', 'rocket' ), '<strong>', '</strong>' );
					?>
				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
					<?php
					// translators: %1$s = opening strong tag, %2$s = closing strong tag.
					printf( esc_html__( 'Easy configuration: the %1$sbest CDN settings%2$s are automatically applied', 'rocket' ), '<strong>', '</strong>' );
					?>
				</li>
				<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
					<?php
					// translators: %1$s = opening strong tag, %2$s = closing strong tag.
					printf( esc_html__( 'WP Rocket integration: the CDN option is %1$sautomatically configured%2$s in our plugin', 'rocket' ), '<strong>', '</strong>' );
					?>
				</li>
			</ul>
			<div class="wpr-rocketcdn-pricing">
				<?php if ( ! empty( $data['error'] ) ) : ?>
				<p><?php echo esc_html( $data['message'] ); ?></p>
				<?php else : ?>
					<?php if ( ! empty( $data['regular_price'] ) ) : ?>
					<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular"><del>$<?php echo esc_html( $data['regular_price'] ); ?></del></h4>
					<?php endif; ?>
					<h4 class="wpr-rocketcdn-pricing-current">
						<?php
						printf(
							// translators: %s = price of RocketCDN subscription.
							esc_html__( '%s / month', 'rocket' ),
							'<span class="wpr-title1">$' . esc_html( $data['current_price'] ) . '</span>'
						);
						?>
					</h4>
					<button class="wpr-button wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal"><?php esc_html_e( 'Get Started', 'rocket' ); ?></button>
				<?php endif; ?>
			</div>
		</div>
	</section>
	<div class="wpr-rocketcdn-cta-footer">
		<a href="https://go.wp-rocket.me/rocket-cdn" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more about Rocket CDN', 'rocket' ); ?></a>
	</div>
	<button class="wpr-rocketcdn-cta-close<?php echo esc_attr( $data['nopromo_variant'] ); ?>"  id="wpr-rocketcdn-close-cta"><span class="screen-reader-text"><?php esc_html_e( 'Reduce this banner', 'rocket' ); ?></span></button>
	<?php if ( ! empty( $data['promotion_campaign'] ) ) : ?>
<p>
	<?php
	printf(
		// translators: %1$s = discounted price, %2$s = regular price.
		esc_html__( '* $%1$s/month for 12 months then $%2$s/month. You can cancel your subscription at any time.', 'rocket' ),
		esc_html( str_replace( '*', '', $data['current_price'] ) ),
		esc_html( $data['regular_price'] )
	);
	?>
</p>
<?php endif; ?>
</div>
