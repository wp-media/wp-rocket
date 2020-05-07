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
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning">
	<div class="components-notice__content">
		<?php if ( ! empty( $data['disabled_description'] ) ) : ?>
			<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
		<?php endif; ?>
	</div>
</div>
<script>
	var cpcss_rest_url       = '<?php echo esc_js( $data['cpcss_rest_url'] ); ?>';
	var cpcss_rest_nonce     = '<?php echo esc_js( $data['cpcss_rest_nonce'] ); ?>';
	var cpcss_generate_btn   = '<?php echo esc_js( __( 'Generate Specific CPCSS', 'rocket' ) ); ?>';
	var cpcss_regenerate_btn = '<?php echo esc_js( __( 'Regenerate specific CPCSS', 'rocket' ) ); ?>';
</script>
