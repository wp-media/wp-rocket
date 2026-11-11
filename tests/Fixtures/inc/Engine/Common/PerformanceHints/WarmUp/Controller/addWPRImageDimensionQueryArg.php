<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => [],
			'url' => 'http://example.com'
		],
		'expected' => 'http://example.com',
	],
	'testShoulDoReturnArgument' => [
		'config' => [
			'filter' => [1],
			'url' => 'http://example.com'
		],
		'expected' => 'http://example.com/?wpr_imagedimensions=1',
	],
	'testShouldAddArgumentWhenNoFactoriesAndRUCSSEnabled' => [
		'config' => [
			'filter' => [],
			'url' => 'http://example.com',
			'remove_unused_css' => 1,
		],
		'expected' => 'http://example.com/?wpr_imagedimensions=1',
	],
];
