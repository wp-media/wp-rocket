<?php

return [
	'testWpengineAddFootprint' => [
		'white_label_footprint' => false,
		'html'                  => '<html><head><title>Sample Page</title>' .
						                '</head><body></body></html>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body></body></html>' .
										"\n" . '<!-- This website is like a Rocket, isn\'t it? Performance optimized by WP Rocket. Learn more: https://wp-rocket.me' . ' -->',
	],
	'testWpengineAddFootprintWithWhitelabel' => [
		'white_label_footprint' => true,
		'html'                  => '<html><head><title>Sample Page</title>' .
										'</head><body></body></html>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body></body></html>' .
										"\n" . '<!-- Optimized for great performance' . ' -->',
	],
	'testWpengineAddFootprintNoHtmlShouldBailOut' => [
		'white_label_footprint' => false,
		'html'                  => '<html><head><title>Sample Page</title>' .
										'</head><body></body>',
		'expected'              => '<html><head><title>Sample Page</title>' .
										'</head><body></body>',
	],
];
