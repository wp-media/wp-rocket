<?php
defined( 'ABSPATH' ) || exit;
?>
<script type="text/template" id="pluginfamily_promote_imagify_uploader_template">
	<div class="pluginfamily-promote-imagify">
		<p>
			<?php
			printf(
				// translators: %1$is = Plugin Name.
				esc_html__( '%1$s recommends you to optimize your images for even better website performance.', '%domain%' ),
				'WP Rocket'
			);
			?>
		</p>
        <button id="pluginfamily_install_imagify"><?php esc_html_e( 'Install Imagify Plugin', '%domain%' ); ?></button>
	</div>
</script>
