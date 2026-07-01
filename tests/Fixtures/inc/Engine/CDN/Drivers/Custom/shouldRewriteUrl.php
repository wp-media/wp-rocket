<?php

return [
	'testShouldReturnTrueForEmptyUrl'    => [
		'config'   => [
			'url' => '',
		],
		'expected' => true,
	],
	'testShouldReturnTrueForRelativeUrl' => [
		'config'   => [
			'url' => '/some/page/',
		],
		'expected' => true,
	],
	'testShouldReturnTrueForAbsoluteUrl' => [
		'config'   => [
			'url' => 'https://example.com/some/page/',
		],
		'expected' => true,
	],
];