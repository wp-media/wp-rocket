<?php

return [
	'testShouldRenderNothingWhenWhiteLabel' => [
		'config'   => [
			'white_label' => true,
			'activated'   => false,
			'api_key'     => false,
		],
		'expected' => [
			'template' => null,
		],
	],
	'testShouldRenderEnabledBannerWhenImagifyIsActivated' => [
		'config'   => [
			'white_label' => false,
			'activated'   => true,
			'api_key'     => false,
		],
		'expected' => [
			'template' => 'partials/imagify-banner-enabled',
		],
	],
	'testShouldRenderPromoBannerWhenImagifyIsNotEnabled' => [
		'config'   => [
			'white_label' => false,
			'activated'   => false,
			'api_key'     => false,
		],
		'expected' => [
			'template' => 'partials/imagify-banner',
		],
	],
	'testShouldRenderEnabledBannerWhenImagifyIsActivatedWithApiKey' => [
		'config'   => [
			'white_label' => false,
			'activated'   => true,
			'api_key'     => true,
		],
		'expected' => [
			'template' => 'partials/imagify-banner-enabled',
		],
	],
	'testShouldRenderNothingWhenApiKeyExistsAndImagifyIsInactive' => [
		'config'   => [
			'white_label' => false,
			'activated'   => false,
			'api_key'     => true,
		],
		'expected' => [
			'template' => null,
		],
	],
];
