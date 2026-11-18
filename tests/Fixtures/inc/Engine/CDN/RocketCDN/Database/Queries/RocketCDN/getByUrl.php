<?php

$item = (object) [ 'id' => 1, 'url' => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8', 'title' => 'Категории' ];

return [
	'testShouldReturnFalseWhenQueryReturnsEmpty'         => [
		'config'   => [
			'url'          => 'https://example.com/my-page',
			'normalized_url' => 'https://example.com/my-page',
			'query_result' => [],
		],
		'expected' => false,
	],
	'testShouldNormalizeRawUnicodeAndReturnItem'         => [
		'config'   => [
			'url'          => 'https://example.com/категории',
			'normalized_url' => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'query_result' => [ $item ],
		],
		'expected' => $item,
	],
	'testShouldBeIdempotentForAlreadyEncodedUrl'         => [
		'config'   => [
			'url'          => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'normalized_url' => 'https://example.com/%D0%BA%D0%B0%D1%82%D0%B5%D0%B3%D0%BE%D1%80%D0%B8%D0%B8',
			'query_result' => [ $item ],
		],
		'expected' => $item,
	],
];
