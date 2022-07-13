<div class="rocket-promo-banner" id="rocket-shutdown-after-banner">
	<div class="rocket-expired-message">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Important: Feature Disabled', 'rocket' ); ?></h3>
		<p>
			<?php
			printf(
			// translators: %1$s = <strong>, %2$s = </strong>.
					esc_html__( 'The Remove Unused CSS feature is %1$snot available%2$s in this version of WP Rocket.', 'rocket' ),
					'<strong>',
					'</strong>'
			);
			?>
		</p>
		<p>
			<?php
			esc_html_e(
				'The option was completely revamped in 3.11 to more easily address the PageSpeed Insights recommendation and improve your page performance. That is why the previous Remove Unused CSS feature has been shut down since.',
				'rocket'
			);
			?>
		</p>
	</div>
</div>
