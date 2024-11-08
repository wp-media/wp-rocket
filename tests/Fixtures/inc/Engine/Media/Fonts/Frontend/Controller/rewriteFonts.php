<?php

return [

	'testShouldCombineV1Font' => [
		'config' => [
			'html' => file_get_contents( __DIR__ . '/HTML/input_v1.php' ),
			'is_allowed' => true,
			'files' => [
				[
					'path' => '/wp-content/cache/wp-rocket/fonts/example.org/70d57d4fc2ebfc2c090d3a76133094d2.css'
				]
			]
		],
		'expected' => file_get_contents( __DIR__ . '/HTML/expected_v1.php' ),
	],
//	'testShouldCombineV2' => [
//		'config' => [
//			'html' => file_get_contents( __DIR__ . '/HTML/input_v2.php' ),
//			'is_allowed' => true,
//		],
//		'expected' =>  file_get_contents( __DIR__ . '/HTML/expected_v2.php' ),
//	],
//	'testShouldCombineV1AndV2' => [
//		'config' => [
//			'html' => file_get_contents( __DIR__ . '/HTML/input_v1_v2.php' ),
//			'is_allowed' => true,
//		],
//		'expected' => file_get_contents( __DIR__ . '/HTML/expected_v1_v2.php' ),
//	],
//	'testShouldBailOutAsNoGoogleFontIncluded' => [
//		'config' => [
//			'html' => '<html><body></body></html>',
//			'is_allowed' => true,
//		],
//		'expected' => '<html><body></body></html>',
//	]
];
