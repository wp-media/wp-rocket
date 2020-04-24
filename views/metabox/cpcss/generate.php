<?php

defined( 'ABSPATH' ) || exit;

?>
<p><?php printf(
		__( 'Generate specific Critical Path CSS for this post. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		); ?></p>
<div class="components-panel__row">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" <?php disabled( $data['disabled'] ); ?>>
		<?php esc_html_e( 'Generate Specific CPCSS', 'rocket' ); ?>
	</button>
</div>
