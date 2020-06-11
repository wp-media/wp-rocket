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

$rocket_cpcss_exists = empty( $data['cpcss_exists'] );
?>
<p class="cpcss_generate <?php echo ! $rocket_cpcss_exists ? 'hidden' : ''; ?>">
<?php
	printf(
		// translators: %1$s = opening link tag, %2$s = closing link tag.
		esc_html__( 'Generate specific Critical Path CSS for this post. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon']['url'] ) . '" data-beacon-article="' . esc_attr( $data['beacon']['id'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		);
	?>
</p>
<p class="cpcss_regenerate <?php echo $rocket_cpcss_exists ? 'hidden' : ''; ?>">
<?php
	printf(
		// translators: %1$s = opening link tag, %2$s = closing link tag.
		esc_html__( 'This post uses specific Critical Path CSS. %1$sMore info%2$s', 'rocket' ),
		'<a href="' . esc_url( $data['beacon']['url'] ) . '" data-beacon-article="' . esc_attr( $data['beacon']['id'] ) . '" target="_blank" rel="noopener noreferrer">',
		'</a>'
		);
	?>
</p>
<div class="components-panel__row cpcss_generate cpcss_regenerate">
	<button id="rocket-generate-post-cpss" class="button components-button is-secondary" <?php disabled( $data['disabled'] ); ?>>
		<span style="display: none;" class="spinner"></span>
		<span class="rocket-generate-post-cpss-btn-txt">
			<?php
			if ( ! $rocket_cpcss_exists ) {
				esc_html_e( 'Regenerate specific CPCSS', 'rocket' );
			} else {
				esc_html_e( 'Generate Specific CPCSS', 'rocket' );
			}
			?>
		</span>
	</button>
</div>
<div class="components-panel__row cpcss_regenerate <?php echo $rocket_cpcss_exists ? 'hidden' : ''; ?>">
	<button id="rocket-delete-post-cpss" class="button components-button is-secondary" <?php disabled( $data['disabled'] ); ?>>
		<span>
			<?php esc_html_e( 'Revert back to the default CPCSS', 'rocket' ); ?>
		</span>
	</button>
</div>
