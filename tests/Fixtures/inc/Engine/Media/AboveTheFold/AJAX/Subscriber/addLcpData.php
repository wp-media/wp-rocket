<?php

return [
	'testShouldBailWhenNotAllowed' => [
		'config'   => [
			'filter'    => false,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => json_encode( [] ),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode( [] ),
				'viewport'      => json_encode( [] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => false,
		],
	],
	'testShouldAddItemToDB' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => false,
			'images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/lcp.jpg',
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => false,
				'status'        => 'completed',
				'lcp'           => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				] ),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
		],
	],
	'testShouldAddItemToDBWhenMobile' => [
		'config'   => [
			'filter'    => true,
			'url'       => 'http://example.org',
			'is_mobile' => true,
			'images'    => json_encode(
				[
					(object) [
						'label' => 'lcp',
						'type'  => 'img',
						'src'   => 'http://example.org/lcp.jpg',
					],
					(object) [
						'label' => 'above-the-fold',
						'type'  => 'img',
						'src'   => 'http://example.org/above-the-fold.jpg',
					],
				]
			),
		],
		'expected' => [
			'item'    => [
				'url'           => 'http://example.org',
				'is_mobile'     => true,
				'status'        => 'completed',
				'lcp'           => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/lcp.jpg',
					],
				] ),
				'viewport'      => json_encode( [
					(object) [
						'type' => 'img',
						'src'  => 'http://example.org/above-the-fold.jpg',
					],
				] ),
				'last_accessed' => '2024-01-01 00:00:00',
			],
			'result'  => true,
		],
	],
];
