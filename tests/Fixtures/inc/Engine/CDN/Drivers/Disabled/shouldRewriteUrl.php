<?php

return [
	'testShouldReturnFalseForEmptyUrl'    => [
		'config'   => [
			'url' => '',
		],
		'expected' => false,
	],
	'testShouldReturnFalseForRelativeUrl' => [
		'config'   => [
			'url' => '/some/page/',
		],
		'expected' => false,
	],
	'testShouldReturnFalseForAbsoluteUrl' => [
		'config'   => [
			'url' => 'https://example.com/some/page/',
		],
		'expected' => false,
	],
];
