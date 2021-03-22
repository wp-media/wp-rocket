<?php

return [

	'shouldBailoutWithNoHTMLContent' => [
		'input' => [
			'html' => '',
		],
		'expected' => [
			'resources' => [],
		],
	],

	'shouldBailoutWithNoResourcesInHTML' => [
		'input' => [
			'html' => '<!DOCTYPE html><html><head><title></title></head><body>Content here</body></html>',
		],
		'expected' => [
			'resources' => [],
		],
	],

	'shouldQueueResources' => [
		'input' => [
			'html' => '<!DOCTYPE html><html><head><title></title>'.
			          '<link rel="stylesheet" type="text/css" href="http://example.org/wp-content/themes/twentytwenty/style.css">'.
			          '<link rel="stylesheet" type="text/css" href="/wp-content/themes/twentytwenty/style.css">'.
			          '</head><body>Content here</body></html>',
		],
		'expected' => [
			'resources' => [],
		],
	],

];
