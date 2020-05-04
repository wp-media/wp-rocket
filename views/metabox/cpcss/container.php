<?php
/**
 * Critical path CSS section in WP Rocket metabox.
 *
 * @since 3.6
 *
 * @data array {
 *     Data to populate the template.
 *
 *     @type string $disabled_description Description to explain why the buttons are disabled.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="inside">
	<h3><?php esc_html_e( 'Critical Path CSS', 'rocket' ); ?></h3>
	<div id="rocket-metabox-cpcss-content">
	<?php do_action( 'rocket_metabox_cpcss_content' ); ?>
	</div>
	<div id="cpcss_response_notice" class="components-notice is-notice">
		<div class="components-notice__content">
		</div>
	</div>
</div>
<?php if ( ! empty( $data['disabled_description'] ) ) : ?>
<div class="components-notice is-notice is-warning">
	<div class="components-notice__content">
		<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
	</div>
</div>
<?php endif; ?>
<script>
	let cpcss_rest_url       = '<?php echo esc_url( $data['cpcss_rest_url'] ); ?>';
	let cpcss_rest_nonce     = '<?php echo esc_html( $data['cpcss_rest_nonce'] ); ?>';
	let cpcss_generate_btn   = '<?php esc_html_e( 'Generate Specific CPCSS', 'rocket' ); ?>';
	let cpcss_regenerate_btn = '<?php esc_html_e( 'Regenerate specific CPCSS', 'rocket' ); ?>';
</script>
