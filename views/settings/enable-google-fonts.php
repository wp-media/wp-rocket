<?php
/**
 * Enable Google Fonts template.
 *
 * @since 3.7
 */

defined( 'ABSPATH' ) || exit;

?>
<div id="wpr-mobile_cpcss_view" class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-stack"><?php esc_html_e( 'Enable Google Font Optimization', 'rocket' ); ?></div>
		<div class="wpr-field-description wpr-hide-on-click"><?php esc_html_e( 'Improves font performance and combines multiple font requests to reduce the number of HTTP requests.', 'rocket' ); ?></div>
		<div class="wpr-field-description wpr-hide-on-click">
			<?php
			printf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				esc_html__( 'This is a one-time action and this button will be removed afterwards. %1$sMore info%2$s', 'rocket' ),
				'<a href="' . esc_url( $data['beacon']['url'] ) . '" data-beacon-article="' . esc_attr( $data['beacon']['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
				);
			?>
		</div>
		<div class="wpr-field-description wpr-field wpr-isHidden wpr-show-on-click">
			<?php
			printf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				esc_html__( 'Google Fonts Optimization is now enabled for your site. %1$sMore info%2$s', 'rocket' ),
				'<a href="' . esc_url( $data['beacon']['url'] ) . '" data-beacon-article="' . esc_attr( $data['beacon']['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
				);
			?>
		</div>
	</div>
	<div class="wpr-tools-col">
		<button id="wpr-action-rocket_enable_google_fonts" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
			<?php esc_html_e( 'Optimize Google Fonts', 'rocket' ); ?>
		</button>
	</div>
</div>
