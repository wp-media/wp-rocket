<?php
/**
 * License banner plan price partial.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p class="wpr-ri-price">
	<span class="wpr-currency"><strong>$</strong></span>
	<span class="wpr-price-number"><?php echo esc_html( $data['price_number'] ); ?></span>
	<span class="wpr-price-decimal"><?php echo esc_html( $data['price_decimal'] ); ?></span>
	<span class="wpr-period">/<strong><?php echo esc_html( $data['period'] ); ?>*</strong></span>
</p>
