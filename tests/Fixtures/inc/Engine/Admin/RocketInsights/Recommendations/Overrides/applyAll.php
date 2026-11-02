<?php

return [
	'test_data' => [
		'shouldReturnUnchangedWhenNoOverrides' => [
			'config'   => [
				'overrides'       => [],
				'recommendations' => [
					[
						'option_slug' => 'plugin_rocketcdn',
						'title'       => 'CDN',
					],
				],
			],
			'expected' => [
				[
					'option_slug' => 'plugin_rocketcdn',
					'title'       => 'CDN',
				],
			],
		],
		'shouldOnlyApplyMatchingSlug'          => [
			'config'   => [
				'overrides'       => [ 'plugin_rocketcdn' => '!' ],
				'recommendations' => [
					[
						'option_slug' => 'plugin_rocketcdn',
						'title'       => 'CDN',
					],
					[
						'option_slug' => 'minify_css',
						'title'       => 'Minify',
					],
				],
			],
			'expected' => [
				[
					'option_slug' => 'plugin_rocketcdn',
					'title'       => 'CDN!',
				],
				[
					'option_slug' => 'minify_css',
					'title'       => 'Minify',
				],
			],
		],
	],
];
