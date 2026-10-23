<?php

$rocket_api_recommendation = [
	'option_slug' => 'plugin_rocketcdn',
	'title'       => 'API title',
	'description' => 'API description',
];

return [
	'test_data' => [
		'shouldNotChangeWhenNothing'               => [
			'config'   => [
				'cdn_state'      => 'nothing',
				'recommendation' => $rocket_api_recommendation,
			],
			'expected' => $rocket_api_recommendation,
		],
		'shouldNotChangeWhenPaid'                  => [
			'config'   => [
				'cdn_state'      => 'rocketcdn_paid',
				'recommendation' => $rocket_api_recommendation,
			],
			'expected' => $rocket_api_recommendation,
		],
		'shouldNotChangeWhenOtherCdn'              => [
			'config'   => [
				'cdn_state'      => 'byocdn',
				'recommendation' => $rocket_api_recommendation,
			],
			'expected' => $rocket_api_recommendation,
		],
		'shouldUpgradeWithExpressCheckoutWhenFree' => [
			'config'   => [
				'cdn_state'      => 'rocketcdn_free',
				'express_url'    => 'https://example.org/checkout',
				'recommendation' => $rocket_api_recommendation,
			],
			'expected' => [
				'option_slug'    => 'plugin_rocketcdn',
				'title'          => 'Upgrade to RocketCDN Pro',
				'description'    => 'Speed up your entire site worldwide by delivering content from servers closer to your visitors.',
				'cta_label'      => 'Upgrade',
				'cta_url'        => 'https://example.org/checkout',
				'cta_class'      => 'wpr-rocketcdn-open',
				'cta_attributes' => [],
			],
		],
		'shouldUpgradeWithModalWhenFreeWithoutExpressCheckout' => [
			'config'   => [
				'cdn_state'      => 'rocketcdn_free',
				'express_url'    => '',
				'recommendation' => $rocket_api_recommendation,
			],
			'expected' => [
				'option_slug'    => 'plugin_rocketcdn',
				'title'          => 'Upgrade to RocketCDN Pro',
				'description'    => 'Speed up your entire site worldwide by delivering content from servers closer to your visitors.',
				'cta_label'      => 'Upgrade',
				'cta_url'        => '',
				'cta_class'      => 'wpr-rocketcdn-open',
				'cta_attributes' => [ 'data-micromodal-trigger' => 'wpr-rocketcdn-modal' ],
			],
		],
	],
];
