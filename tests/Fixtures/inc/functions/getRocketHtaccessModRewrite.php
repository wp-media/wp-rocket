<?php

return [
	'test_data' => [
		'shouldReferenceWebpEnvWhenWebpCacheEnabled' => [
			'config'   => [
				'cache_webp'       => 1,
				'webp_rewritecond' => null,
			],
			'expected' => [
				'contains'     => [
					'RewriteRule .* - [E=WPR_WEBP:-webp]',
					'/index%{ENV:WPR_SSL}%{ENV:WPR_WEBP}.html',
				],
				'not_contains' => [],
			],
		],
		'shouldNotReferenceWebpEnvWhenWebpCacheDisabled' => [
			'config'   => [
				'cache_webp'       => 0,
				'webp_rewritecond' => null,
			],
			'expected' => [
				'contains'     => [
					'/index%{ENV:WPR_SSL}.html',
				],
				'not_contains' => [
					'WPR_WEBP',
				],
			],
		],
		'shouldNotReferenceWebpEnvWhenWebpRulesFilteredOut' => [
			'config'   => [
				'cache_webp'       => 1,
				'webp_rewritecond' => '',
			],
			'expected' => [
				'contains'     => [
					'/index%{ENV:WPR_SSL}.html',
				],
				'not_contains' => [
					'WPR_WEBP',
				],
			],
		],
	],
];
