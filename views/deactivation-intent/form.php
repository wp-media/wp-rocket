<?php
/**
 * Deactivation intent form template.
 *
 * @since 3.0
 *
 * $data array {
 *     Data to populate the form.
 *
 *     @type string $form_action URL to submit the deactivation form.
 * }
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wpr-modal" id="wpr-deactivation-modal" aria-hidden="true">
	<div class="wpr-modal-overlay" tabindex="-1" data-micromodal-close>
		<div class="wpr-modal-container" role="dialog" aria-modal="true" aria-labelledby="wpr-deactivation-modal-title" >
			<header class="wpr-modal-header">
				<h2 class="wpr-modal-title" id="wpr-deactivation-modal-title"><?php esc_html_e( 'Facing an issue?', 'rocket' ); ?></h2>
			</header>
			<div>
				<p><?php esc_html_e( 'It is not always necessary to deactivate WP Rocket when facing any issues. Most of them can be fixed by deactivating only some options.', 'rocket' ); ?></p>
				<p>
					<?php
					printf(
						// translators: %1$s = opening strong tag, %2$s = closing strong tag.
						esc_html__( 'Our advice? Instead of deactivating WP Rocket, use our %1$sSafe Mode%2$s to quickly disable LazyLoad, File Optimization, and CDN options. Then check to see if your issue is resolved.', 'rocket' ),
						'<strong>',
						'</strong>'
					);
					?>
				</p>
				<p><strong><?php esc_html_e( 'Do you want to use our Safe Mode to troubleshoot WP Rocket?', 'rocket' ); ?></strong></p>
				<form method="post" action="<?php echo esc_attr( $data['form_action'] ); ?>">
					<ul>
						<li>
							<input type="radio" id="safe_mode" value="safe_mode" name="mode" checked />
							<label for="safe_mode">
								<?php
								printf(
									// translators: %1$s = opening strong tag, %2$s = closing strong tag.
									esc_html__( 'Yes, apply "%1$sSafe Mode%2$s"', 'rocket' ),
									'<strong>',
									'</strong>'
								);
								?>
							</label>
						</li>
						<li>
							<input type="radio" id="deactivate" value="deactivate" name="mode" /><label for="deactivate"><?php esc_html_e( 'No, deactivate and snooze this message for', 'rocket' ); ?></label>
							<select name="snooze">
								<option value="1"><?php esc_html_e( '1 day', 'rocket' ); ?></option>
								<option value="7"><?php esc_html_e( '7 days', 'rocket' ); ?></option>
								<option value="30"><?php esc_html_e( '30 days', 'rocket' ); ?></option>
								<option value="0"><?php esc_html_e( 'Forever', 'rocket' ); ?></option>
							</select>
						</li>
					</ul>
					<?php wp_nonce_field( 'rocket_deactivation' ); ?>
					<div class="wpr-modal-footer">
						<button aria-label="Close modal" class="wpr-modal-button wpr-modal-cancel" data-micromodal-close><?php esc_html_e( 'Cancel', 'rocket' ); ?></button>
						<input type="submit" class="wpr-modal-button wpr-modal-confirm" value="<?php esc_attr_e( 'Confirm', 'rocket' ); ?>" />
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
