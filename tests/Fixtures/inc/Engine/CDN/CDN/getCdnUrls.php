<?php

return [
	'returnsAllZoneMatchingCnames'          => [
		'config'   => [
			'zones'      => [ 'all' ],
			'cdn_cnames' => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
			'cdn_zone'   => [ 'all', 'all' ],
		],
		'expected' => [
			'cdn_urls' => [ 'https://cdn1.example.com', 'https://cdn2.example.com' ],
		],
	],
	// Task 9.3 regression: no reachability probe is performed anymore, so a
	// host that would previously have failed CNAMEValidator::is_valid() is
	// still returned unfiltered — CNAME correctness is now the user's
	// responsibility, not just for the RocketCDN context but for BYOCDN too.
	'returnsAllZoneMatchingCnamesWithoutReachabilityFiltering' => [
		'config'   => [
			'zones'      => [ 'all' ],
			'cdn_cnames' => [ 'https://cdn1.example.com', 'https://unreachable.example.com' ],
			'cdn_zone'   => [ 'all', 'all' ],
		],
		'expected' => [
			'cdn_urls' => [ 'https://cdn1.example.com', 'https://unreachable.example.com' ],
		],
	],
	'returnsEmptyArrayWhenNoCdnCnamesSet'   => [
		'config'   => [
			'zones'      => [ 'all' ],
			'cdn_cnames' => [],
			'cdn_zone'   => [],
		],
		'expected' => [
			'cdn_urls' => [],
		],
	],
];
