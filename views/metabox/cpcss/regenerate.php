<?php

defined( 'ABSPATH' ) || exit;

$disabled = isset( $data['disabled'] ) ? $data['disabled'] : '';
?>
<p><?php printf(
		__( 'This post uses specific Critical Path CSS. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		); ?></p>
<button id="rocket-generate-post-cpss" class="components-button is-link" <?php echo esc_attr( $disabled ); ?>>
	<?php esc_html_e( 'Regenerate specific CPCSS', 'rocket' ); ?>
</button>
<button id="rocket-delete-post-cpss" class="components-button is-link is-destructive" <?php echo esc_attr( $disabled ); ?>>
	<?php esc_html_e( 'revert back to the default CPCSS', 'rocket' ); ?>
</button>
<?php if ( ! empty( $disabled ) ) : ?>
<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
<?php endif; ?>
