<?php

return [
	'testShouldReturnSameWhenFeatureDisabled' => [
		'config'     => [
			'filter'                  => false,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'lcp'      => '',
				'viewport' => '',
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
		],
		'exclusions' => [
			'foo',
		],
		'expected'   => [
			'foo',
		],
	],
	'testShouldReturnSameWhenEmptyRow' => [
		'config'     => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => false,
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
		],
		'exclusions' => [
			'foo',
		],
		'expected'   => [
			'foo',
		],
	],
	'testShouldReturnUpdatedWhenRowExists' => [
		'config'     => [
			'filter'                  => true,
			'wp'                      => (object) [
				'request' => '',
			],
			'url'                     => 'http://example.org',
			'is_mobile'               => false,
			'row'                     => (object) [
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'https://example.com/bar.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'https://example.com/foobar.jpg',
					],
				] ),
			],
			'cache_mobile'            => 0,
			'do_caching_mobile_files' => 0,
			'wp_is_mobile'            => false,
		],
		'exclusions' => [
			'foo',
		],
		'expected'   => [
			'foo',
			'bar.jpg',
			'foobar.jpg',
		],
	],
];
