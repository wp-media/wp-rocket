<?php

return [
	'test_data' => [
		'testShouldReturnFalseWhenError' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
			],
			'result' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'Critical CSS for http://example.org/ not generated. Error: No valid stylesheets available'
			],
			'transient' => [
				'total'     => 1,
				'items' => [
					'front_page.css' => [
						'status' => [
							'nonmobile' => [
								'message' => 'Critical CSS for http://example.org/ not generated. Error: No valid stylesheets available',
								'success' => false,
							],
						],
					],
				],
			],
		],

		'testShouldReturnFalseAndSetTransientWhenPending' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
			],
			'result' => [
				'success' => true,
				'code'    => 'cpcss_generation_pending',
				'message' => 'pending',
			],
			'transient' => null,
		],

		'testShouldReturnFalseWhenSuccess' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
			],
			'result' => [
				'success' => true,
				'code'    => 'cpcss_generation_successful',
				'message' => 'success',
			],
			'transient' => [
				'total'     => 1,
				'items' => [
					'front_page.css' => [
						'status' => [
							'nonmobile' => [
								'message' => 'success',
								'success' => true,
							],
						],
					],
				],
			],
		],
	],
];
