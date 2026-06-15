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
 *      @type string $regular_price_monthly RocketCDN regular monthly price.
 *      @type string $regular_price_annual RocketCDN regular annual price.
 *      @type string $current_price_monthly RocketCDN current monthly price.
 *      @type string $current_price_annual RocketCDN current annual price.
 *      @type string $cta_heading CTA toggle text with strong-wrapped heading.
 *      @type string $cta_description CTA description text.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

$rocketcdn_container_classes = [ 'wpr-rocketcdn-cta' ];

if ( empty( $data['is_visible'] ) ) {
	$rocketcdn_container_classes[] = 'wpr-isHidden';
}

if ( ! empty( $data['is_visible'] ) && ! empty( $data['is_expanded'] ) ) {
	$rocketcdn_container_classes[] = 'wpr-rocketcdn-cta--expanded';
	$rocketcdn_container_classes[] = 'wpr-rocketcdn-cta---max-limit';
}

if ( ! empty( $data['is_visible'] ) && empty( $data['is_expanded'] ) ) {
	$rocketcdn_container_classes[] = 'wpr-rocketcdn-cta--collapsed';
}

if ( ! empty( $data['container_class'] ) ) {
	$rocketcdn_container_classes[] = $data['container_class'];
}

?>

<div class="<?php echo esc_attr( implode( ' ', $rocketcdn_container_classes ) ); ?>" id="wpr-rocketcdn-cta">
	<div class="wpr-rocketcdn-cta-toggle wpr-rocketcdn-cta-toggle--default" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">
		<p class="wpr-rocketcdn-cta-toggle__text">
			<?php echo wp_kses( $data['cta_heading'], [ 'strong' => [] ] ); ?>
		</p>
		<span class="wpr-rocketcdn-cta-toggle__icon" aria-hidden="true"></span>
	</div>

	<div class="wpr-rocketcdn-cta-toggle wpr-rocketcdn-cta-toggle--max-limit" role="button" tabindex="0" aria-controls="wpr-rocketcdn-cta-expandable" aria-expanded="true">
		<span class="wpr-rocketcdn-cta-toggle__check" aria-hidden="true"></span>
		<p class="wpr-rocketcdn-cta-toggle__text">
			<?php echo wp_kses( $data['cta_heading_max_limit'], [ 'strong' => [] ] ); ?>
			<br />
			<span><?php echo esc_html( $data['cta_description'] ); ?></span>
		</p>
		<span class="wpr-rocketcdn-cta-toggle__icon" aria-hidden="true"></span>
	</div>

	<div class="wpr-rocketcdn-cta-separator" aria-hidden="true"></div>

	<div class="wpr-rocketcdn-cta-expandable" id="wpr-rocketcdn-cta-expandable">
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
		<section class="wpr-rocketcdn-cta-content<?php echo esc_attr( $data['nopromo_variant'] ); ?> wpr-flex">
			<div class="wpr-rocketcdn-content">
				<h3 class="wpr-title2">
					<?php echo esc_html( 'RocketCDN' ); ?>
					<span class="wpr-badge wpr-badge--blue"><?php esc_html_e( 'Pro', 'rocket' ); ?></span>
				</h3>
				<ul class="wpr-rocketcdn-features">
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-bandwidth">
						<?php
						// translators: %1$s = opening strong tag, %2$s = closing strong tag.
						printf( esc_html__( 'Faster content delivery across %1$syour entire website%2$s', 'rocket' ), '<strong>', '</strong>' );
						?>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-configuration">
						<?php
						// translators: %1$s = opening strong tag, %2$s = closing strong tag.
						printf( esc_html__( '%1$sNo bandwidth limits%2$s, no hidden costs', 'rocket' ), '<strong>', '</strong>' );
						?>
					</li>
					<li class="wpr-rocketcdn-feature wpr-rocketcdn-automatic">
						<?php
						// translators: %1$s = opening strong tag, %2$s = closing strong tag.
						printf( esc_html__( '%1$s100+ edge locations%2$s for wider global coverage', 'rocket' ), '<strong>', '</strong>' );
						?>
					</li>
					<li class="wpr-rocketcdn-cta-footer">
						<a href="https://wp-rocket.me/rocketcdn-pro/" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more about RocketCDN', 'rocket' ); ?></a>
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
						<div class="wpr-rocketcdn-pricing--content">
							<div class="wpr-rocketcdn-pricing--toggle">
								<input type="checkbox" class="wpr-rocketcdn-toggle--input" id="wpr-rocketcdn-toggle-input">
								<label class="wpr-rocketcdn-toggle" for="wpr-rocketcdn-toggle-input">
									<span class="wpr-rocketcdn-toggle--slider"></span>
									<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--inactive"><?php esc_html_e( 'Monthly', 'rocket' ); ?></span>
									<span class="wpr-rocketcdn-pricing--toggle-label wpr-rocketcdn-pricing--toggle-label--active"><?php esc_html_e( 'Yearly', 'rocket' ); ?></span>
								</label>
								<div class="wpr-rocketcdn-pricing--badge"><?php esc_html_e( '2 Months Free!', 'rocket' ); ?></div>
							</div>
							<div class="wpr-rocketcdn-pricing--price-container">
								<?php if ( ! empty( $data['regular_price_monthly'] ) ) : ?>
								<h4 class="wpr-title2 wpr-rocketcdn-pricing-regular">
									<del>
										<span class="wpr-rocketcdn-pricing-regular-price wpr-rocketcdn-pricing-regular-price--monthly">$<?php echo esc_html( $data['regular_price_monthly'] ); ?></span>
										<span class="wpr-rocketcdn-pricing-regular-price wpr-rocketcdn-pricing-regular-price--yearly wpr-isHidden">$<?php echo esc_html( $data['regular_price_annual'] ); ?></span>
									</del>
								</h4>
								<?php endif; ?>

								<div class="wpr-rocketcdn-pricing--price">
									<span class="wpr-rocketcdn-pricing--currency">$</span>
									<?php
									// Handle both period and comma as decimal separators for i18n.
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, not a global.
									$monthly_decimal_pos = max( (int) strpos( $data['current_price_monthly'], '.' ), (int) strpos( $data['current_price_monthly'], ',' ) );
									// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Template variable, not a global.
									$annual_decimal_pos = max( (int) strpos( $data['current_price_annual'], '.' ), (int) strpos( $data['current_price_annual'], ',' ) );
									?>
									<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--monthly"><?php echo esc_html( substr( $data['current_price_monthly'], 0, $monthly_decimal_pos ) ); ?></span>
									<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--monthly"><?php echo esc_html( substr( $data['current_price_monthly'], $monthly_decimal_pos ) ); ?></span>

									<span class="wpr-rocketcdn-pricing--amount wpr-rocketcdn-pricing--annual wpr-isHidden"><?php echo esc_html( substr( $data['current_price_annual'], 0, $annual_decimal_pos ) ); ?></span>
									<span class="wpr-rocketcdn-pricing--cents wpr-rocketcdn-pricing--annual wpr-isHidden"><?php echo esc_html( substr( $data['current_price_annual'], $annual_decimal_pos ) ); ?></span>
								</div>
								<div class="wpr-rocketcdn-pricing--billing">
									<div class="wpr-rocketcdn-pricing--billing-period">
										<span class="wpr-rocketcdn-pricing--billing-period--monthly"><?php esc_html_e( 'Billed Monthly.', 'rocket' ); ?></span>
										<span class="wpr-rocketcdn-pricing--billing-period--yearly wpr-isHidden"><?php esc_html_e( 'per month, billed yearly', 'rocket' ); ?></span>
									</div>
									<span class="wpr-rocketcdn-pricing--billing-vat">(<?php esc_html_e( 'excl. VAT', 'rocket' ); ?>)</span>
								</div>
							</div>

							<button class="wpr-button wpr-rocketcdn-pricing--cta wpr-rocketcdn-open"<?php echo empty( $data['button_url'] ) ? ' data-micromodal-trigger="wpr-rocketcdn-modal"' : ''; ?>><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></button>
						</div>
						
					<?php endif; ?>
				</div>
		</section>
	</div>
</div>
