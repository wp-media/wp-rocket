<?php

return [
	'returnsAllUrlsWhenAllCnamesAreValid'   => [
		'config'   => [
			'zones'         => [ 'all' ],
			'cdn_cnames'    => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
			'cdn_zone'      => [ 'all', 'all' ],
			'validator_map' => [
				'https://cdn1.example.com' => true,
				'https://cdn2.example.com' => true,
			],
		],
		'expected' => [
			'cdn_urls' => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
		],
	],
	'filtersOutCnameWhenValidatorReturnsFalse' => [
		'config'   => [
			'zones'         => [ 'all' ],
			'cdn_cnames'    => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
			'cdn_zone'      => [ 'all', 'all' ],
			'validator_map' => [
				'https://cdn1.example.com' => false,
				'https://cdn2.example.com' => true,
			],
		],
		'expected' => [
			'cdn_urls' => [ 'https://cdn2.example.com' ],
		],
	],
	'returnsEmptyArrayWhenAllCnamesInvalid' => [
		'config'   => [
			'zones'         => [ 'all' ],
			'cdn_cnames'    => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
			'cdn_zone'      => [ 'all', 'all' ],
			'validator_map' => [
				'https://cdn1.example.com' => false,
				'https://cdn2.example.com' => false,
			],
		],
		'expected' => [
			'cdn_urls' => [],
		],
	],
	'returnsEmptyArrayWhenNoCdnCnamesSet'   => [
		'config'   => [
			'zones'         => [ 'all' ],
			'cdn_cnames'    => [],
			'cdn_zone'      => [],
			'validator_map' => [],
		],
		'expected' => [
			'cdn_urls' => [],
		],
	],
];
