<?php

return [

	'testShouldDisplayNothingWhenWhiteLabel' => [
		'config'   => [
			'white_label'      => true,
			'home_url'         => 'http://localhost',
			'rocketcdn_status' => null,
		],
		'expected' => '',
	],

	'testShouldDisplayNothingWhenNotLiveSite' => [
		'config'   => [
			'home_url'         => 'http://localhost',
			'rocketcdn_status' => null,
		],
		'expected' => '',
	],

	'testShouldNotRenderButtonHTMLWhenSubscriptionInactive' => [
		'config'   => [
			'home_url'         => 'http://example.org',
			'rocketcdn_status' => [ 'subscription_status' => 'cancelled' ],
		],
		'expected'         => '',
	],

	'testShouldRenderButtonHTMLWhenSubscriptionActive' => [
		'config'   => [
			'home_url'         => 'http://example.org',
			'rocketcdn_status' => [ 'subscription_status' => 'running' ],
		],
		'expected'         => <<<HTML
<p class="wpr-rocketcdn-subscription">
	<button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button>
</p>
HTML
		,
	],
];
