<?php

return [
	'test_data' => [
		'testShouldReturnOriginalWhenNotAllowed' => [
			'config' => [
				'is_allowed' => false,
			],
			'original' => '<html><body></body></html>',
			'expected' => '<html><body></body></html>',
		],
		'testShouldReturnOriginalWhenNoGoogleFonts' => [
		'config' => [
			'is_allowed' => true,
		],
		'original' => '<html><body></body></html>',
		'expected' => '<html><body></body></html>',
	],
		'testShouldRewriteV1Font' => [
			'config' => [
				'is_allowed' => true,
			],
			'original' => file_get_contents( __DIR__ . '/HTML/input_v1.php' ),
			'expected' => file_get_contents( __DIR__ . '/HTML/expected_v1.php' ),
		],
		'testShouldRewriteV2' => [
			'config' => [
				'is_allowed' => true,
			],
			'original' => file_get_contents( __DIR__ . '/HTML/input_v2.php' ),
			'expected' =>  file_get_contents( __DIR__ . '/HTML/expected_v2.php' ),
		],
		'testShouldRewriteV1AndV2' => [
			'config' => [
				'is_allowed' => true,
			],
			'original' => file_get_contents( __DIR__ . '/HTML/input_v1_v2.php' ),
			'expected' => file_get_contents( __DIR__ . '/HTML/expected_v1_v2.php' ),
		],
	],
];
