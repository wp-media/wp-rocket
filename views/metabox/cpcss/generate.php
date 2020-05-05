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
		esc_html__( 'Generate specific Critical Path CSS for this post. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		);
	?>
</p>
<div class="components-panel__row">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" <?php disabled( $data['disabled'] ); ?>>
		<?php esc_html_e( 'Generate Specific CPCSS', 'rocket' ); ?>
	</button>
</div>
