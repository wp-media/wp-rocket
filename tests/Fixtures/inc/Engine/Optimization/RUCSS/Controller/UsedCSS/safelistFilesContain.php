<?php

return [

	'test_data' => [
		'shouldBailoutWithEmptySafelist' => [
			'config' => [
				'url' => 'https://example.org/wp-content/css/file1.css',
				'safelist' => [],
			],
			'expected' => false,
		],

		'shouldBailoutWithSafelistNotHavingFiles' => [
			'config' => [
				'url' => 'https://example.org/wp-content/css/file1.css',
				'safelist' => [
					'.classname',
					'#idname',
					'tagname',
				],
			],
			'expected' => false,
		],

		'shouldReturnFalseWhenPatternNotMatched' => [
			'config' => [
				'url' => 'https://example.org/wp-content/css/file1.css',
				'safelist' => [
					'.classname',
					'#idname',
					'tagname',
					'/wp-content/wrong.css'
				],
			],
			'expected' => false,
		],

		'shouldReturnTrueWhenPatternMatched' => [
			'config' => [
				'url' => 'https://example.org/wp-content/css/file1.css',
				'safelist' => [
					'.classname',
					'#idname',
					'tagname',
					'/wp-content/(.*).css'
				],
			],
			'expected' => true,
		],

	],

];
