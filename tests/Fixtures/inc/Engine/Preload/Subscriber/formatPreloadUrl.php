<?php
return [
	'noIndexShouldReturnSame' => [
		'config' => [
			'url' => '/test/'
		],
		'expected' => '/test/',
	],
	'indexShouldRemove' => [
		'config' => [
			'url' => '/test/index.html'
		],
		'expected' => '/test/',
	],
	'indexHttpsShouldRemove' => [
		'config' => [
			'url' => '/test/index-https.html'
		],
		'expected' => '/test/',
	],
	'indexGzipShouldRemove' => [
		'config' => [
			'url' => '/test/index.html_gzip'
		],
		'expected' => '/test/',
	],
	'indexHttpsGzipShouldRemove' => [
		'config' => [
			'url' => '/test/index-https.html_gzip'
		],
		'expected' => '/test/',
	],
];
