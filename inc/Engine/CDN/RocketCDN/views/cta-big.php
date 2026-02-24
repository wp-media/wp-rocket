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

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wpr-rocketcdn-cta <?php echo esc_attr( $data['container_class'] ); ?>" id="wpr-rocketcdn-cta">
	<?php if ( ! empty( $data['promotion_campaign'] ) ) : ?>
	<div class="wpr-flex wpr-rocketcdn-promo">
		<h3 class="wpr-rocketcdn-promo-title"><?php echo esc_html( $data['promotion_campaign'] ); ?></h3>
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
		<div class="wpr-flex">	
			<div class="wpr-rocketcdn-card-left">
				<div class="wpr-rocketcdn-header">
				<h2 class="wpr-rocketcdn-header--title"><?php esc_html_e( 'Propel your Content at the Speed of Light!', 'rocket' ); ?></h2>
				<p class="wpr-rocketcdn-header--subtitle"><?php esc_html_e( 'RocketCDN delivers your content from servers around the world for a faster website.', 'rocket' ); ?></p>
				</div>
					<ul class="wpr-rocketcdn-features">
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
							<div class="wpr-rocketcdn-feature--content">
								<h3 class="wpr-rocketcdn-feature--title"><?php esc_html_e( 'Unlimited Performance', 'rocket' ); ?></h3>
								<p class="wpr-rocketcdn-feature--description"><?php esc_html_e( 'Experience blazing-fast content delivery through 120 edge locations with unlimited bandwidth.', 'rocket' ); ?></p>
							</div>
						</li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
							<div class="wpr-rocketcdn-feature--content">
								<h3 class="wpr-rocketcdn-feature--title"><?php esc_html_e( 'Pre-Tuned for Speed', 'rocket' ); ?></h3>
								<p class="wpr-rocketcdn-feature--description"><?php esc_html_e( 'Enjoy pre-configured settings tailored for maximum speed and performance.', 'rocket' ); ?></p>
							</div>
						</li>
						<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
							<div class="wpr-rocketcdn-feature--content">
								<h3 class="wpr-rocketcdn-feature--title"><?php esc_html_e( 'Effortless Setup', 'rocket' ); ?></h3>
								<p class="wpr-rocketcdn-feature--description"><?php esc_html_e( 'Benefit from automatic configuration of the CDN option in WP Rocket, making setup effortless.', 'rocket' ); ?></p>
							</div>
						</li>
						<li class="wpr-rocketcdn-cta-footer">
							<div class="wpr-rocketcdn-cta-footer--cancel-notice">
								<span><?php esc_html_e( 'You can cancel anytime!', 'rocket' ); ?></span>
							</div>
						</li>
						<?php if ( ! empty( $data['promotion_campaign'] ) ) : ?>
							<li class="wpr-rocketcdn-cta-promo-footer">
								<?php
								printf(
								// translators: %1$s = discounted price, %2$s = regular price.
									esc_html__( '*$%1$s/month for 12 months then $%2$s/month. You can cancel your subscription at any time.', 'rocket' ),
									esc_html( str_replace( '*', '', $data['current_price'] ) ),
									esc_html( $data['regular_price'] )
								);
								?>
							</li>
						<?php endif; ?>
					</ul>
			</div>
			<div class="wpr-rocketcdn-pricing">
				<?php if ( ! empty( $data['error'] ) ) : ?>
					<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php else : ?>

				<div class="wpr-rocketcdn-pricing--logo">
					<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'rocketcdn-logo.svg' ); ?>" alt="Rocket Logo" class="wpr-rocketcdn-pricing--logo-icon">
					<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'rocketcdn-text.svg' ); ?>" alt="RocketCDN Text" class="wpr-rocketcdn-pricing--logo-text">
				</div>

				<div class="wpr-rocketcdn-pricing--content">

					<div class="wpr-rocketcdn-pricing--toggle">
						<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive"><?php esc_html_e( 'Monthly', 'rocket' ); ?></span>
						<label class="wpr-rocketcdn-toggle">
							<input type="checkbox" class="wpr-rocketcdn-toggle--input" checked>
							<span class="wpr-rocketcdn-toggle--slider"></span>
						</label>
						<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active"><?php esc_html_e( 'Yearly', 'rocket' ); ?></span>
					</div>

					<div class="wpr-rocketcdn-pricing--badge"><?php esc_html_e( '2 Months Free!', 'rocket' ); ?></div>

					<div class="wpr-rocketcdn-pricing--price-container">
						<div class="wpr-rocketcdn-pricing--price">
							<span class="wpr-rocketcdn-pricing--currency">$</span>
							<span class="wpr-rocketcdn-pricing--amount"><?php echo esc_html( substr( $data['current_price'], 0, strpos( $data['current_price'], '.' ) ) ); ?></span>
							<span class="wpr-rocketcdn-pricing--cents"><?php echo esc_html( substr( $data['current_price'], strpos( $data['current_price'], '.' ) ) ); ?></span>
						</div>
						<div class="wpr-rocketcdn-pricing--billing">
							<span class="wpr-rocketcdn-pricing--billing-period"><?php esc_html_e( 'per month, billed yearly', 'rocket' ); ?></span>
							<span class="wpr-rocketcdn-pricing--billing-vat">(<?php esc_html_e( 'excl. VAT', 'rocket' ); ?>)</span>
						</div>
					</div>

					<button class="wpr-rocketcdn-pricing--cta wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal"><?php esc_html_e( 'Get Started', 'rocket' ); ?></button>
				</div>
				<?php endif; ?>
			</div>
			<button class="wpr-rocketcdn-cta-close<?php echo esc_attr( $data['nopromo_variant'] ); ?>" id="wpr-rocketcdn-close-cta"><span class="screen-reader-text"><?php esc_html_e( 'Reduce this banner', 'rocket' ); ?></span></button>
		</div>
	</section>
</div>
