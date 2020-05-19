<?php
/**
 * Activate CPCSS mobile template.
 *
 * @since 3.6
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-check2"><?php esc_html_e( 'Enable CPCSS for mobiles text', 'rocket' ); ?></div>
		<div class="wpr-field-description"><?php esc_html_e( 'Description for CPCSS for mobiles. Probably some doc link.', 'rocket' ); ?></div>
	</div>
	<div class="wpr-tools-col">
		<button id="wpr-action-rocket_enable_mobile_cpcss" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
			<?php esc_html_e( 'Enable CPCSS for mobile', 'rocket' ); ?>
		</button>
	</div>
</div>
