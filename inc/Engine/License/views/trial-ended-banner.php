<?php
/**
 * Trial ended banner.
 *
 * @since 3.23.4
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="wpr-trial-ended-banner">
	<a href="<?php echo esc_url( $data['renewal_url'] ); ?>">
		<?php esc_html_e( 'Get WP Rocket back', 'rocket' ); ?>
	</a>
</div>