<?php
/**
 * RocketCDN reseller limit reached banner template.
 *
 * Displayed when a reseller account has reached the 3-page RocketCDN Free limit.
 * No upgrade CTA is shown — reseller accounts are not eligible for paid plans.
 *
 * @since 3.23.1
 *
 * @param array $data {
 *     @type string $heading     Banner heading text.
 *     @type string $description Banner description text.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound

?>

<div class="wpr-rocketcdn-cta wpr-rocketcdn-cta---max-limit<?php echo ! empty( $data['is_hidden'] ) ? ' wpr-isHidden' : ''; ?>" id="wpr-rocketcdn-reseller-limit-cta">
	<div class="wpr-rocketcdn-cta-toggle wpr-rocketcdn-cta-toggle--max-limit" role="status">
		<span class="wpr-rocketcdn-cta-toggle__check" aria-hidden="true"></span>
		<p class="wpr-rocketcdn-cta-toggle__text">
			<strong><?php echo esc_html( $data['heading'] ); ?></strong>
			<br />
			<span><?php echo esc_html( $data['description'] ); ?></span>
		</p>
	</div>
</div>
