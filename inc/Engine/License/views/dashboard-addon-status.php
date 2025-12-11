<?php
/**
 * Add-On license status on dashboard tab template.
 *
 * @since 3.20
 *
 * @param array $data {
 *    @type bool   $is_live_site    Identifies if the current website is a live or local/staging one.
 *    @type string $container_class Flex container CSS class.
 *    @type string $label           Content label.
 *    @type string $status_class    CSS Class to display the status.
 *    @type string $status_text     Text to display the subscription status.
 *    @type bool   $is_active       Boolean identifying the activation status.
 *    @type string $service_name    Name of the service to display.
 *    @type string $upgrade_link    Link to the upgrade page.
 *    @type string $upgrade_text    Text for the upgrade button.
 * }
 */

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php echo esc_html( $data['service_name'] ); ?></h3>
</div>
<div class="wpr-field wpr-field-account">
	<?php if ( ! $data['is_live_site'] ) : ?>
	<span class="wpr-infoAccount wpr-isInvalid">
		<?php
		printf(
		/* translators: %1$s = domain. */
		esc_html__( '%s is unavailable on local domains and staging sites.', 'rocket' ),
		esc_html( $data['service_name'] )
												);
		?>
		</span>
	<?php else : ?>
	<div class="wpr-flex<?php echo esc_attr( $data['container_class'] ); ?>">
		<div>
			<span class="wpr-title3"><?php echo esc_html( $data['label'] ); ?></span>
			<span class="wpr-infoAccount<?php echo esc_attr( $data['status_class'] ); ?>"><?php echo esc_html( $data['status_text'] ); ?></span>
		</div>
		<?php if ( key_exists( 'upgrade_link', $data ) ) : ?>
		<div>
			<a href="<?php echo esc_url( $data['upgrade_link'] ); ?>" class="wpr-button"><?php echo esc_html( $data['upgrade_text'] ); ?></a>
		</div>
		<?php endif; ?>
	</div>
	<?php endif; ?>
</div>
