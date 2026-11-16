<?php

return [
	'testShouldTrackLearnMoreOnNotEnabledBanner' => [
		'config'   => [
			'template' => 'partials/imagify-banner',
		],
		'expected' => [
			'contains'     => [
				'class="wpr-imagifyBanner-learnMore" data-mixpanel-button="imagify banner learn more"',
			],
			'not_contains' => [
				'data-mixpanel-button="imagify banner install"',
				'data-mixpanel-button="imagify banner open settings"',
			],
		],
	],

	'testShouldTrackOpenSettingsOnEnabledBanner' => [
		'config'   => [
			'template' => 'partials/imagify-banner-enabled',
		],
		'expected' => [
			'contains'     => [
				'data-mixpanel-button="imagify banner open settings"',
			],
			'not_contains' => [
				'data-mixpanel-button="imagify banner install"',
				'data-mixpanel-button="imagify banner learn more"',
			],
		],
	],
];
