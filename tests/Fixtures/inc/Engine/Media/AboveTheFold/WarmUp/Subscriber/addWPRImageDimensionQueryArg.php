<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
			'url' => 'http://example.com'
		],
		'expected' => 'http://example.com',
	],
	'testShoulDoReturnArgument' => [
		'config' => [
			'filter' => true,
			'url' => 'http://example.com'
		],
		'expected' => 'http://example.com/?wpr_imagedimensions=1',
	],
];
