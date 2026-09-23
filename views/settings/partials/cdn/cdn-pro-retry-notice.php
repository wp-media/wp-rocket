<?php
/**
 * Pro manual retry notice partial.
 *
 * Renders a notice banner prompting the user to manually retry their cdn pro activation
 * to continue using RocketCDN.
 *
 * @param array $data {
 *     Notice data.
 *
 *     @type string $retry_url URL to the retry the pro activation.
 * }
 *
 * @since 3.23.4
 */

?>

<div class="wpr-rocketcdn-notice wpr-cdn-expired__notice wpr-field" id="wpr-cdn-licence-banner">
	<div class="wpr-notice-container wpr-flex">
		<div class="wpr-notice-70">
			<p>
				<?php
					printf(
						// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
						esc_html__( '%1$sOops, we couldn\'t confirm your RocketCDN Pro subscription%2$s. No worries: let\'s do a manual check.', 'rocket' ),
						'<strong>',
						'</strong>'
					);
					?>
			</p>
		</div>
		
		<a target="_blank" id="wpr-rocketcdn-retry-pro-detection" rel="noopener noreferrer" class="wpr-rocketcdn-btn wpr-rocketcdn-btn_retry" href="<?php echo esc_url( $data['retry_url'] ); ?>">
			<?php esc_html_e( 'Retry', 'rocket' ); ?>
		</a>
	
	</div>
</div>
