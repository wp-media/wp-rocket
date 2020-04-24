<?php

defined( 'ABSPATH' ) || exit;

?>
<div class="inside">
	<h3><?php esc_html_e( 'Critical Path CSS', 'rocket' ); ?></h3>
	<div id="rocket-metabox-cpcss-notice"></div>
	<div id="rocket-metabox-cpcss-content">
	<?php do_action( 'rocket_metabox_cpcss_content' ); ?>
	</div>
</div>
<?php if ( ! empty( $data['disabled_description'] ) ) : ?>
<div class="components-notice is-notice is-warning">
	<div class="components-notice__content">
		<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
	</div>
</div>
<?php endif; ?>

