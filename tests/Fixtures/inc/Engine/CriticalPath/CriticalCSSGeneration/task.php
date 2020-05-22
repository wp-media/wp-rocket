<?php

return [
	'test_data' => [
		'testShouldReturnFalseWhenError' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
				'mobile' => 0,
			],
			'result' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'error',
			],
			'transient' => [
				'generated' => 0,
				'total'     => 1,
				'items'     => [
					'error',
				],
			],
			'expected' => false,
		],

		'testShouldReturnFalseWhenTimeout' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 11,
				'mobile' => 0,
			],
			'result' => [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => 'error',
			],
			'transient' => [
				'generated' => 0,
				'total'     => 1,
				'items'     => [
					'error',
				],
			],
			'expected' => false,
		],

		'testShouldReturnItemWhenPending' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
				'mobile' => 0,
			],
			'result' => [
				'success' => true,
				'code'    => 'cpcss_generation_pending',
				'message' => 'pending',
			],
			'transient' => false,
			'expected' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 1,
				'mobile' => 0,
			],
		],

		'testShouldReturnFalseWhenSuccess' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 0,
				'mobile' => 0,
			],
			'result' => [
				'success' => true,
				'code'    => 'cpcss_generation_successful',
				'message' => 'success',
			],
			'transient' => [
				'generated' => 1,
				'total'     => 1,
				'items'     => [
					'success',
				],
			],
			'expected' => false,
		],
	],
];
