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
				'generated' => 0,
				'total'     => 1,
				'items'     => [
					'Critical CSS for http://example.org/ not generated. Error: No valid stylesheets available'
				],
			],
			'expected' => false,
		],

		'testShouldReturnFalseWhenTimeout' => [
			'item' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 11,
			],
			'result' => [
				'success' => false,
				'code'    => 'cpcss_generation_timeout',
				'message' => 'Critical CSS for http://example.org/ timeout. Please retry a little later.',
			],
			'transient' => [
				'generated' => 0,
				'total'     => 1,
				'items'     => [
					'Critical CSS for http://example.org/ timeout. Please retry a little later.',
				],
			],
			'expected' => false,
		],

		'testShouldReturnItemWhenPending' => [
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
			'expected' => [
				'url'    => 'http://example.org/',
				'path'   => 'front_page.css',
				'check'  => 1,
			],
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
