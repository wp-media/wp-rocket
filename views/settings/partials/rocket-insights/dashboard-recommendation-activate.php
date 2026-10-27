<?php
/**
 * Dashboard recommendation Activate link.
 *
 * @param array $data Formatted recommendation.
 */

defined( 'ABSPATH' ) || exit;

$rocket_cta_label = ! empty( $data['cta_label'] ) ? $data['cta_label'] : __( 'Activate', 'rocket' );
$rocket_cta_url   = $data['cta_url'] ?? $data['section'];
$rocket_cta_class = trim( 'wpr-dash-recs__activate wpr-recommendation-item__activate ' . ( $data['cta_class'] ?? '' ) );
$rocket_cta_attrs = '';

foreach ( (array) ( $data['cta_attributes'] ?? [] ) as $rocket_cta_attr => $rocket_cta_value ) {
	$rocket_cta_attrs .= ' ' . sanitize_key( $rocket_cta_attr ) . '="' . esc_attr( $rocket_cta_value ) . '"';
}
?>
<?php if ( '' !== $rocket_cta_url ) : ?>
<a class="<?php echo esc_attr( $rocket_cta_class ); ?>" href="<?php echo esc_url( $rocket_cta_url ); ?>" data-recommendation="<?php echo esc_attr( $data['option_slug'] ); ?>"<?php echo $rocket_cta_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built above. ?>><?php echo esc_html( $rocket_cta_label ); ?></a>
<?php else : ?>
<button type="button" class="<?php echo esc_attr( $rocket_cta_class ); ?>" data-recommendation="<?php echo esc_attr( $data['option_slug'] ); ?>"<?php echo $rocket_cta_attrs; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped when built above. ?>><?php echo esc_html( $rocket_cta_label ); ?></button>
<?php endif; ?>
