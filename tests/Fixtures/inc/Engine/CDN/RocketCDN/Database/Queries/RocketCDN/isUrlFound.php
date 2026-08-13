<?php

return [
	'testShouldReturnTrueWhenRawUnicodeUrlIsFound'       => [
		'config'   => [
			'url'            => 'https://example.com/категории',
			'normalized_url' => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'query_result'   => 1,
		],
		'expected' => true,
	],
	'testShouldReturnTrueWhenAlreadyEncodedUrlIsFound'   => [
		'config'   => [
			'url'            => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'normalized_url' => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'query_result'   => 1,
		],
		'expected' => true,
	],
	'testShouldReturnFalseWhenUrlIsNotFound'             => [
		'config'   => [
			'url'            => 'https://example.com/my-page',
			'normalized_url' => 'https://example.com/my-page',
			'query_result'   => 0,
		],
		'expected' => false,
	],
];
