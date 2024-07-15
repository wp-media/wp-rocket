<?php
/**
 * RocketCDN status on dashboard tab template.
 *
 * @since 3.5
 *
 * @param array $data {
 *    @type bool   $is_live_site    Identifies if the current website is a live or local/staging one
 *    @type string $container_class Flex container CSS class.
 *    @type string $label           Content label.
 *    @type string $status_class    CSS Class to display the status.
 *    @type string $status_text     Text to display the subscription status.
 *    @type bool   $is_active       Boolean identifying the activation status.
 * }
 */

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">RocketCDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<?php if ( ! $data['is_live_site'] ) : ?>
	<span class="wpr-infoAccount wpr-isInvalid"><?php esc_html_e( 'RocketCDN is unavailable on local domains and staging sites.', 'rocket' ); ?></span>
	<?php else : ?>
	<div class="wpr-flex<?php echo esc_attr( $data['container_class'] ); ?>">
		<div>
			<span class="wpr-title3"><?php echo esc_html( $data['label'] ); ?></span>
			<span class="wpr-infoAccount<?php echo esc_attr( $data['status_class'] ); ?>"><?php echo esc_html( $data['status_text'] ); ?></span>
		</div>
		<?php if ( ! $data['is_active'] ) : ?>
		<div>
			<a href="#page_cdn" class="wpr-button"><?php esc_html_e( 'Get RocketCDN', 'rocket' ); ?></a>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
