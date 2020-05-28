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
					'front_page.css' => 'Critical CSS for http://example.org/ not generated. Error: No valid stylesheets available'
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
				'generated' => 1,
				'total'     => 1,
				'items'     => [
					'front_page.css' => 'success',
				],
			],
		],
	],
];
