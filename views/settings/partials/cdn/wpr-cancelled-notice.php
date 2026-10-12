<?php
/**
 * Pro user cancelled notice.
 *
 * Renders a notice banner informing the user that RocketCDN is being cancelled.
 *
 * @since 3.23.3
 */

?>

<div class="wpr-rocketcdn-notice wpr-cdn-expired__notice" id="wpr-cdn-cancelled-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-70">
			<p>
			<?php
				printf(
					// translators: %1$s = opening <strong> tag, %2$s = closing </strong> tag.
					esc_html__( '%1$sRocketCDN Pro subscription is being cancelled.%2$s RocketCDN Pro can’t be reactivated until the process is complete.', 'rocket' ),
					'<strong>',
					'</strong>'
				);
				?>
			</p>
		</div>
	</div>
</div>
