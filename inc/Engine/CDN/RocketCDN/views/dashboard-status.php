<?php
/**
 * RocketCDN status on account tab template.
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
<div class="wpr-account-section">
	<div class="wpr-account-header">
		<h3 class="wpr-account-title">
			<?php esc_html_e( 'RocketCDN Account', 'rocket' ); ?>
		</h3>
	</div>
	<div class="wpr-account-card">
	<?php if ( ! $data['is_live_site'] ) : ?>
		<span class="wpr-account-value wpr-isInvalid"><?php esc_html_e( 'RocketCDN is unavailable on local domains and staging sites.', 'rocket' ); ?></span>
	<?php else : ?>
		<div class="wpr-account-row wpr-account-row--split wpr-flex<?php echo esc_attr( $data['container_class'] ); ?>">
			<div class="wpr-account-plan wpr-dashboard-plans">
			<?php foreach ( $data['items'] ?? [] as $rocket_plan_item ) : ?>
				<div class="wpr-account-planItem">
					<span class="wpr-account-planLabel"><?php echo esc_html( '' !== $rocket_plan_item['label'] ? $rocket_plan_item['label'] : __( 'License', 'rocket' ) ); ?></span>
					<span class="wpr-account-planValue<?php echo esc_attr( $rocket_plan_item['class'] ); ?>"><?php echo esc_html( $rocket_plan_item['value'] ); ?></span>
				</div>
			<?php endforeach; ?>
			</div>
			<?php if ( ! $data['is_active'] ) : ?>
			<div class="wpr-account-actions">
				<a href="#page_cdn" class="wpr-account-button"><?php esc_html_e( 'Get RocketCDN Pro', 'rocket' ); ?></a>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>
	</div>
</div>
