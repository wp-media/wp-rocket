<?php

return [
	'test_data' => [
		'shouldSetResponseAndReturnTrueOnSuccess' => [
			'atts' => [
				'url'     => 'http://example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => 'h1 {color: red;}',
			],
			'success' => true,
			'expected' => 'This is the response body',
		],
		'shouldSetErrorAndReturnFalseOnFail' => [
			'atts' => [
				'url'     => 'http://example.com/path/to/styles.css',
				'type'    => 'css',
				'content' => 'h1 {color: red;}',
			],
			'success' => false,
			'expected' => 'Remove Unused CSS API is unavailable',
		]
	],
];
