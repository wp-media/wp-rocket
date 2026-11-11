<?php
/**
 * Mobile Cache Template.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-export"><?php esc_html_e( 'Mobile Cache', 'rocket' ); ?></div>
		<div class="wpr-field-description" id="wpr_mobile_cache_default">
			<?php
			echo esc_html__( 'Speed your site for mobile visitors.', 'rocket' );
			?>
			<br>
			<?php
			printf(
				// translators: %1$s = opening link tag, %2$s = closing link tag.
				esc_html__( 'This is a one-time action and this button will be removed afterwards. %1$sMore info%2$s', 'rocket' ),
				'<a href="' . esc_url( $data['url'] ) . '" data-beacon-article="' . esc_attr( $data['id'] ) . '" target="_blank" rel="noopener noreferrer">',
				'</a>'
			);
			?>
		</div>
		<div class="wpr-field-description wpr-field wpr-isHidden" id="wpr_mobile_cache_response">
			<?php
			echo esc_html__( 'Mobile Cache is now enabled for your site.', 'rocket' );
			?>
		</div>
	</div>
	<div class="wpr-tools-col">
		<button id="wpr_enable_mobile_cache" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
			<?php esc_html_e( 'Enable Mobile Cache', 'rocket' ); ?>
		</button>
	</div>
</div>
