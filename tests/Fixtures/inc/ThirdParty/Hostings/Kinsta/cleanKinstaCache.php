<?php
return [
	'shouldEmptyCacheWhenExisting' => [
		'config' => [
			'has_cache' => true,
		],
		'expected' => true
	],
	'ShouldDoNothingWhenNoCache' => [
		'config' => [
			'has_cache' => false,
		],
		'expected' => false
	]
];
