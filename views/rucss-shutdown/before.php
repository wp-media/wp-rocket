<div class="rocket-promo-banner" id="rocket-shutdown-before-banner">
	<div class="rocket-expired-message">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Important: Feature Shutting Down Soon', 'rocket' ); ?></h3>
		<p>
			<?php
			printf(
			// translators: %1$s = <strong>, %2$s = </strong>, %3$s = date.
					esc_html__( 'You’re running on an old version of WP Rocket whose %1$sRemove Unused CSS feature%2$s will be shut down %1$sfrom %3$s.%2$s', 'rocket' ),
					'<strong>',
					'</strong>',
					$data['formatted_date']
			);
			?>
		</p>
		<p>
			<?php esc_html_e( 'This option was completely revamped in 3.11 to more easily address the PageSpeed Insights recommendation
			and improve your page performance.', 'rocket' ); ?>
		</p>
		<p>
			<?php
			printf(
			// translators: %1$s = <strong>, %2$s = discount percentage, %3$s = </strong>.
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
