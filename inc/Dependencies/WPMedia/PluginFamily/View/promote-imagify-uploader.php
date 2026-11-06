<?php
defined( 'ABSPATH' ) || exit;
?>
<script type="text/template" id="pluginfamily_promote_imagify_uploader_template">
	<div class="pluginfamily-promote-imagify">
		<button type="button" class="pluginfamily-promote-imagify-dismiss">
			<svg class="pluginfamily-promote-imagify-dismiss-icon" viewBox="0 0 24 24" fill="currentColor">
				<path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
			</svg>
		</button>
		<?php if ( ! empty( $notice ) ) { ?>
			<p>
				<?php echo esc_html( $notice ); ?>
			</p>
		<?php } ?>
		<button id="pluginfamily_install_imagify"><?php esc_html_e( 'Install Imagify Plugin', 'rocket' ); ?></button>
	</div>
</script>
