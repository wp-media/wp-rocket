<?php
/**
 * License banner plan price partial.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p class="wpr-pma-price">
	<span class="wpr-currency">$</span>
	<span class="wpr-price-number"><?php echo esc_html( $data['price_number'] ); ?></span>
	<span class="wpr-price-decimal"><?php echo esc_html( $data['price_decimal'] ); ?></span>
	<span class="wpr-period">/<?php echo esc_html( $data['period'] ); ?>*</span>
</p>
