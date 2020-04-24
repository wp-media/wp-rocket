<?php

defined( 'ABSPATH' ) || exit;

?>
<p><?php printf(
		__( 'This post uses specific Critical Path CSS. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		); ?></p>
<div class="components-panel__row">
	<button id="rocket-generate-post-cpss" class="components-button is-link" <?php disabled( $data['disabled'] ); ?>>
		<?php esc_html_e( 'Regenerate specific CPCSS', 'rocket' ); ?>
	</button>
</div>
<div class="components-panel__row">
	<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" <?php disabled( $data['disabled'] ); ?>>
		<?php esc_html_e( 'Revert back to the default CPCSS', 'rocket' ); ?>
	</button>
</div>

