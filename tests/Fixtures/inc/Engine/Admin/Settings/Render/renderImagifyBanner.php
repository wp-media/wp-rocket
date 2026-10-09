<?php

return [
	'testShouldRenderNothingWhenWhiteLabel' => [
		'config'   => [
			'white_label' => true,
			'activated'   => false,
		],
		'expected' => [
			'template' => null,
		],
	],
	'testShouldRenderEnabledBannerWhenImagifyIsActivated' => [
		'config'   => [
			'white_label' => false,
			'activated'   => true,
		],
		'expected' => [
			'template' => 'partials/imagify-banner-enabled',
		],
	],
	'testShouldRenderPromoBannerWhenImagifyIsNotEnabled' => [
		'config'   => [
			'white_label' => false,
			'activated'   => false,
		],
		'expected' => [
			'template' => 'partials/imagify-banner',
		],
	],
];
