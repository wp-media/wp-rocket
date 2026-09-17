<?php

return [
	'testShouldReturnEarlyWhenNoLcp' => [
		'config'   => [
			'has_lcp' => false,
			'lcp'     => '',
		],
		'html'     => '<html><head></head><body></body></html>',
		'expected' => '<html><head></head><body></body></html>',
	],
	'testShouldAddFetchpriorityWhenPregReplaceCallbackSucceeds' => [
		'config'   => [
			'has_lcp' => true,
			'lcp'     => json_encode(
				(object) [
					'type' => 'img',
					'src'  => 'image.jpg',
				]
			),
		],
		'html'     => '<html><head><title>Test</title></head><body><img src="image.jpg" alt="test"></body></html>',
		'expected' => '<html><head><title>Test</title><link rel="preload" data-rocket-preload as="image" href="image.jpg" fetchpriority="high"></head><body><img fetchpriority="high" src="image.jpg" alt="test"></body></html>',
	],
];
