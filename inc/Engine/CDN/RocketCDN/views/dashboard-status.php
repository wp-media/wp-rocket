<?php
/**
 * RocketCDN status on dashboard tab template.
 *
 * @since 3.5
 *
 * @param array $data {
 *    @type bool   $is_live_site    Identifies if the current website is a live or local/staging one.
 *    @type string $container_class Flex container CSS class.
 *    @type bool   $is_active       Boolean identifying the activation status.
 *    @type array  $items           List of plan info rows, each with 'label', 'value', and 'class'.
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
		<div class="wpr-dashboard-plans">
		<?php foreach ( $data['items'] ?? [] as $rocket_plan_item ) : ?>
		<div>
			<span class="wpr-title3"><?php echo esc_html( $rocket_plan_item['label'] ); ?></span>
			<span class="wpr-infoAccount<?php echo esc_attr( $rocket_plan_item['class'] ); ?>"><?php echo esc_html( $rocket_plan_item['value'] ); ?></span>
		</div>
		<?php endforeach; ?>
		</div>
		<?php if ( ! $data['is_active'] ) : ?>
		<div>
			<a href="#page_cdn" class="wpr-button"><?php esc_html_e( 'Get RocketCDN Pro', 'rocket' ); ?></a>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
