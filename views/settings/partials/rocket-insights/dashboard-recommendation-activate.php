<?php
/**
 * Dashboard recommendation Activate link.
 *
 * @param array $data Formatted recommendation.
 */

defined( 'ABSPATH' ) || exit;
?>
<a class="wpr-dash-recs__activate wpr-recommendation-item__activate" href="<?php echo esc_url( $data['section'] ); ?>" data-recommendation="<?php echo esc_attr( $data['option_slug'] ); ?>"><?php esc_html_e( 'Activate', 'rocket' ); ?></a>
