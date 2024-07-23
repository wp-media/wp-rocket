<?php

return [
	'testShouldCallATFQueueOnce' => [
		'config' => [
			'url' => 'http://example.com',
			'device' => 'desktop',
			'get_rocket_option' => 'true'
		],
		'expected' => 'http://example.com',
	],
	'testShouldCallATFQueueTwice' => [
		'config' => [
			'device' => 'mobile',
			'url' => 'http://example.com',
			'get_rocket_option' => ''
		],
		'expected' => 'http://example.com/?wpr_imagedimensions=1',
	],
];
