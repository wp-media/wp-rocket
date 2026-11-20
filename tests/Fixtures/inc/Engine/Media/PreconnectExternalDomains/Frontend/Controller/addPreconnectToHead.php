<?php

$existing_item = [
	'open_tag' => '<link',
	'href'     => 'https://example.org/style.css',
	'rel'      => 'stylesheet',
];

$prefetch = function ( $href ) {
	return [
		'open_tag' => '<link',
		'href'     => $href,
		0          => 'data-rocket-prefetch',
		'rel'      => 'dns-prefetch',
	];
};

$preconnect = function ( $href ) {
	return [
		'open_tag' => '<link',
		'href'     => $href,
		0          => 'crossorigin',
		1          => 'data-rocket-preconnect',
		'rel'      => 'preconnect',
	];
};

return [
	'testShouldReturnItemsWhenNotAllowed'                  => [
		'config'   => [
			'is_allowed' => false,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode( [ 'https://cdn.example.org' ] ),
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [ $existing_item ],
	],
	'testShouldReturnItemsWhenNoRow'                       => [
		'config'   => [
			'is_allowed' => true,
			'row'        => null,
			'items'      => [ $existing_item ],
		],
		'expected' => [ $existing_item ],
	],
	'testShouldReturnItemsWhenRowNotCompleted'             => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'failed',
				'domains' => json_encode( [ 'https://cdn.example.org' ] ),
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [ $existing_item ],
	],
	'testShouldRenderStoredOriginsUnchanged'               => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode( [ 'https://fonts.gstatic.com', 'https://cdn.example.org:8443' ] ),
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [
			$existing_item,
			$prefetch( 'https://fonts.gstatic.com' ),
			$prefetch( 'https://cdn.example.org:8443' ),
		],
	],
	'testShouldSkipOldEntriesWithPathQueryOrFragment'      => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode(
					[
						'https://external.test/a/b.css#rel=stylesheet',
						'https://cdn.example.org',
						'https://external.test/x?//fonts.googleapis.com/css2',
					]
				),
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [
			$existing_item,
			$prefetch( 'https://cdn.example.org' ),
		],
	],
	'testShouldReturnItemsWhenAllOldEntriesAreInvalid'     => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode(
					[
						'https://external.test/x?//fonts.googleapis.com/css2',
						'https://external.test/a/b.css#rel=stylesheet',
						'',
						'ftp://cdn.example.org',
					]
				),
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [ $existing_item ],
	],
	'testShouldReturnItemsWhenDomainsAreNotJson'           => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => 'not-json',
			],
			'items'      => [ $existing_item ],
		],
		'expected' => [ $existing_item ],
	],
	'testShouldRenderDuplicatesOnce'                       => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode( [ 'https://cdn.example.org/', 'https://cdn.example.org', 'HTTPS://CDN.EXAMPLE.ORG' ] ),
			],
			'items'      => [],
		],
		'expected' => [
			$prefetch( 'https://cdn.example.org' ),
		],
	],
	'testShouldSkipNonStringEntries'                       => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => json_encode( [ [ 'https://external.test' ], 42, null, 'https://cdn.example.org' ] ),
			],
			'items'      => [],
		],
		'expected' => [
			$prefetch( 'https://cdn.example.org' ),
		],
	],
	'testShouldRenderOldRowStoredAsObject'                 => [
		'config'   => [
			'is_allowed' => true,
			'row'        => [
				'status'  => 'completed',
				'domains' => '{"2":"https:\/\/cdn.example.org","3":"https:\/\/external.test\/a.css"}',
			],
			'items'      => [],
		],
		'expected' => [
			$prefetch( 'https://cdn.example.org' ),
		],
	],
	'testShouldUsePreconnectWhenPrefetchFilterIsFalse'     => [
		'config'   => [
			'is_allowed'   => true,
			'row'          => [
				'status'  => 'completed',
				'domains' => json_encode( [ 'https://cdn.example.org/', 'https://external.test/a/b.css#rel=stylesheet' ] ),
			],
			'use_prefetch' => false,
			'items'        => [],
		],
		'expected' => [
			$preconnect( 'https://cdn.example.org' ),
		],
	],
];
