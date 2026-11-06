<?php

return [
	'testShouldReturnTrueWhenUrlIsFoundInDatabase'    => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'is_found' => true,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenUrlIsNotFoundInDatabase' => [
		'config'   => [
			'url'      => 'https://example.com/page/',
			'is_found' => false,
		],
		'expected' => false,
	],
];