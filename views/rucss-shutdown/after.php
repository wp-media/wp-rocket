<div class="rocket-promo-banner" id="rocket-shutdown-after-banner">
	<div class="rocket-expired-message">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Important: Feature Disabled', 'rocket' ); ?></h3>
		<p>
			<?php
			printf(
			// translators: %1$s = <strong>, %2$s = </strong>.
					esc_html__( 'The Remove Unused CSS feature is %1$sno longer available%2$s to expired users running on an old version of WP Rocket.', 'rocket' ),
					'<strong>',
					'</strong>'
			);
			?>
		</p>
		<p>
			<?php esc_html_e( 'This feature is a great option to address the PageSpeed Insights recommendation and improve your website performance,
			but the option was enhanced and you now need a more recent version to use it.', 'rocket' ); ?>
		</p>
		<p>
			<?php
			printf(
			// translators: %1$s = <strong>, %2$s = discount percentage, %3$s = </strong>, %4$s = discount price.
					esc_html__( '%1$sRenew your license now and get %2$s OFF%3$s to update your WP Rocket version!', 'rocket' ),
					'<strong>',
					$data['discount_percentage'] . '%',
					'</strong>'
			);
			?>
		</p>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer">
			<?php esc_html_e( 'Renew now', 'rocket' ); ?>
		</a>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal">
		<span class="screen-reader-text">
			<?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?>
		</span>
	</button>
</div>
