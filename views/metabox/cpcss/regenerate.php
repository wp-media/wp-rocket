<?php
/**
 * Critical path CSS generate template.
 *
 * @since 3.6
 *
 * @data array {
 *     Data to populate the template.
 *
 *     @type string $beacon   Helpscout documentation link.
 *     @type bool   $disabled True if button should be disabled, false otherwise.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<p>
<?php
	printf(
		// translators: %1$s = opening link tag, %2$s = closing link tag.
		esc_html__( 'This post uses specific Critical Path CSS. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		);
	?>
</p>
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
