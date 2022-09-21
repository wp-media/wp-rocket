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
	'indexGzipShouldRemove' => [
		'config' => [
			'url' => '/test/index.html_gzip'
		],
		'expected' => '/test/',
	],
];
