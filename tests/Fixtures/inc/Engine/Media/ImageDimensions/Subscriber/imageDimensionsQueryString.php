<?php

return [
	'testShouldReturnSameWhenNoQueryString' => [
		'config' => [
			'query_string' => '',
		],
		'html' => '<img src="image.jpg">',
		'expected' => '<img src="image.jpg">',
	],
	'testShouldReturnUpdated' => [
		'config' => [
			'query_string' => 'wpr_imagedimensions',
		],
		'html' => '<img src="image.jpg">',
		'expected' => '<img src="image.jpg" width="100" height="100">',
	],
];
