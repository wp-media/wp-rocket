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
];
