<?php

defined( 'ABSPATH' ) || exit;

$disabled = isset( $data['disabled'] ) ? $data['disabled'] : '';
?>
<p><?php printf(
		__( 'Generate specific Critical Path CSS for this post. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		); ?></p>
<button id="rocket-generate-post-cpss" class="components-button is-secondary" <?php echo esc_attr( $disabled ); ?>>
	<?php esc_html_e( 'Generate Specific CPCSS', 'rocket' ); ?>
</button>
<?php if ( ! empty( $disabled ) ) : ?>
<p><?php echo esc_html( $data['disabled_description'] ); ?></p>
<?php endif; ?>
