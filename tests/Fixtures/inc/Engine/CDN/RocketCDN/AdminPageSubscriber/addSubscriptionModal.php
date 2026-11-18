<?php

// Mirrors the button URL construction in AdminPageSubscriber::add_subscription_modal(),
// so this fixture stays correct regardless of the environment's configured site URL.
$single_quote_button_url = add_query_arg(
	'dashboard_url',
	rawurlencode(
		add_query_arg(
			[
				'page'               => WP_ROCKET_PLUGIN_SLUG,
				'rocketcdn_checkout' => 'true',
			],
			admin_url( 'options-general.php' )
		)
	),
	esc_url_raw( 'https://example.com/checkout/o\'reilly' )
);
$single_quote_button_url_json = wp_json_encode( $single_quote_button_url );

return [

	'testShouldDisplayNothingWhenWhiteLabel' => [
		'config'   => [
			'white_label' => true,
			'home_url'    => 'http://localhost',
		],
		'expected' => '',
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'config'   => [
			'home_url' => 'http://localhost',
		],
		'expected' => '',
	],

	'testShouldDisplayModalWithProductionURL' => [
		'config' => [
			'home_url' => 'http://example.org',
		],
		'expected' => <<<HTML
<script type="text/javascript">
	window.rocketcdnButtonUrl = "";
</script>
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
	<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="wpr-loader" id="wpr-rocketcdn-modal-loader"></div>
		<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
			<div id="wpr-rocketcdn-modal-content">
				<iframe id="rocketcdn-iframe" data-src="https://api.wp-rocket.me/cdn/iframe?website=http://example.org&#038;callback=http://example.org/index.php?rest_route=/wp-rocket/v1/rocketcdn/&#038;source=plugin" loading="lazy" width="674" height="425"></iframe>			</div>
		</div>
	</div>
</div>
HTML
	],

	'testShouldEscapeSingleQuoteInButtonUrl' => [
		'config' => [
			'home_url'  => 'http://example.org',
			'user_data' => [
				'rocketcdn' => [
					'button' => [
						'url' => 'https://example.com/checkout/o\'reilly',
					],
				],
			],
		],
		'expected' => <<<HTML
<script type="text/javascript">
	window.rocketcdnButtonUrl = {$single_quote_button_url_json};
</script>
<div class="wpr-rocketcdn-modal" id="wpr-rocketcdn-modal" aria-hidden="true">
	<div class="wpr-rocketcdn-modal__overlay" tabindex="-1" data-micromodal-close>
		<div class="wpr-loader" id="wpr-rocketcdn-modal-loader"></div>
		<div class="wpr-rocketcdn-modal__container" role="dialog" aria-modal="true" aria-labelledby="wpr-rocketcdn-modal-title">
			<div id="wpr-rocketcdn-modal-content">
				<iframe id="rocketcdn-iframe" data-src="https://api.wp-rocket.me/cdn/iframe?website=http://example.org&#038;callback=http://example.org/index.php?rest_route=/wp-rocket/v1/rocketcdn/&#038;source=plugin" loading="lazy" width="674" height="425"></iframe>			</div>
		</div>
	</div>
</div>
HTML
	],
];
