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

$rocket_disabled_description = empty( $data['disabled_description'] );
?>
<div class="inside">
	<h3><?php esc_html_e( 'Critical Path CSS', 'rocket' ); ?></h3>
	<div id="rocket-metabox-cpcss-content">
		<?php do_action( 'rocket_metabox_cpcss_content' ); ?>
	</div>
</div>
<div id="cpcss_response_notice" class="components-notice is-notice is-warning<?php echo $rocket_disabled_description ? ' hidden' : ''; ?>">
	<div class="components-notice__content">
		<?php if ( ! $rocket_disabled_description ) : ?>
			<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
		<?php endif; ?>
	</div>
</div>
