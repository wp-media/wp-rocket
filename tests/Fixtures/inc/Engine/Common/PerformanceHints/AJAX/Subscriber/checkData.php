<?php

return [
	'testShouldReturnErrorWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'post'      => [
				'rocket_beacon_nonce' => wp_create_nonce( 'rocket_beacon' ),
				'action'           => 'rocket_check_beacon',
				'url'              => 'http://example.org',
				'is_mobile'        => false,
			],
			'row' => [],
		],
		'expected' => [
			'result'  => true,
		],
	],
	'testShouldReturnExists' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'post'      => [
				'rocket_beacon_nonce' => wp_create_nonce( 'rocket_beacon' ),
				'action'           => 'rocket_check_beacon',
				'url'              => 'http://example.org',
				'is_mobile'        => false,
			],
			'row' => [
				'status' => 'completed',
				'url' => 'http://example.org',
				'lcp'      => json_encode( (object) [
					'type' => 'img',
					'src'  => 'http://example.org/wp-content/uploads/image.jpg',
				] ),
				'viewport' => json_encode( [
					0 => (object) [
						'type' => 'img',
						'src'  => 'http://example.org/wp-content/uploads/image.jpg',
					],
				] ),
			],
			'row_exists' => true,
		],
		'expected' => [
			'result'  => true,
		],
	],
	'testShouldReturnNotExists' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'post'      => [
				'rocket_beacon_nonce' => wp_create_nonce( 'rocket_beacon' ),
				'action'           => 'rocket_check_beacon',
				'url'              => 'http://example.org',
				'is_mobile'        => false,
			],
			'row' => [],
			'row_exists' => false,
		],
		'expected' => [
			'result'  => false,
		],
	],
];
