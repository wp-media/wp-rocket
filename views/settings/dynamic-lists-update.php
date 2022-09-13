<?php
/**
 * Dynamic lists update template.
 *
 * @since 3.11.4
 */

defined( 'ABSPATH' ) || exit;

?>

<div class="wpr-tools">
	<div class="wpr-tools-col">
		<div class="wpr-title3 wpr-tools-label wpr-icon-export"><?php esc_html_e( 'Update Inclusion and Exclusion Lists', 'rocket' ); ?></div>
		<div class="wpr-field-description"><?php esc_html_e( 'Update Inclusion and Exclusion Lists', 'rocket' ); ?></div>
		<div id="wpr-update-exclusion-msg" class="wpr-field-description"></div>
	</div>
	<div class="wpr-tools-col">
		<button id="wpr-update-exclusion-list" class="wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-refresh">
			<?php esc_html_e( 'Update lists', 'rocket' ); ?>
		</button>
	</div>
</div>
