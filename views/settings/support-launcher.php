<?php
/**
 * Floating link to the contact page, shown when the support chat is not available.
 *
 * @param array $data {
 *     Link data.
 *
 *     @type string $url URL of the contact page.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_label = __( 'Ask Support', 'rocket' );

?>
<a class="wpr-support-launcher" href="<?php echo esc_url( $data['url'] ); ?>" target="_blank" rel="noopener noreferrer" title="<?php echo esc_attr( $rocket_label ); ?>">
	<svg class="wpr-support-launcher__icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
	<span class="wpr-support-launcher__label"><?php echo esc_html( $rocket_label ); ?></span>
	<span class="screen-reader-text"><?php esc_html_e( '(opens in a new tab)', 'rocket' ); ?></span>
</a>
