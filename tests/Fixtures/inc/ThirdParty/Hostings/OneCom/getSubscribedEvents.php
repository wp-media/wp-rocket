<?php

return [
	'test_data' => [
		'shouldHideRocketCdnRecommendation' => [
			'config'   => [ 'hook' => 'rocket_display_rocketcdn_recommendation' ],
			'expected' => 'return_false',
		],
		'shouldHideRocketCdnCta'            => [
			'config'   => [ 'hook' => 'rocket_display_rocketcdn_cta' ],
			'expected' => 'return_false',
		],
	],
];
