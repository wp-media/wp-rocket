<?php

$fleet = [
	'issuer'       => 'grn:2@int:grn::environment/g1:wprocket:fleet.localhost',
	'jwks_url'     => 'https://fleet.example/api/v1/grnd/jwks',
	'crm_issuer'   => 'grn:2@int:grn::environment/g1:wprocket:app.localhost',
	'crm_jwks_url' => 'https://wp-rocket.me/wp-json/grnd/v1/jwks',
];

$nothing = [
	'command_issuer' => '',
	'consent_issuer' => '',
	'command_keys'   => '',
	'consent_keys'   => '',
];

$everything = [
	'command_issuer' => $fleet['issuer'],
	'consent_issuer' => $fleet['crm_issuer'],
	'command_keys'   => $fleet['jwks_url'],
	'consent_keys'   => $fleet['crm_jwks_url'],
];

return [
	// The shape get_remote_settings_data() returns on a cache miss: the whole
	// decoded response, settings under `data`.
	'testShouldReadTheBlockWhenNestedUnderData'         => [
		'config'   => [
			'response' => (object) [ 'data' => (object) [ 'fleet' => (object) $fleet ] ],
		],
		'expected' => $everything,
	],

	// And the shape it returns on a cache hit: what was stored, which is
	// already the inner `data`. Reading one shape only works for half the
	// calls, and which half depends on transient expiry — so getting this
	// wrong looks intermittent rather than wrong.
	'testShouldReadTheBlockWhenAtTheTopLevel'           => [
		'config'   => [
			'response' => (object) [ 'fleet' => (object) $fleet ],
		],
		'expected' => $everything,
	],

	// An install that has never successfully polled. It must trust nobody
	// rather than fall back to a default issuer.
	'testShouldTrustNobodyWhenThereIsNoResponse'        => [
		'config'   => [ 'response' => false ],
		'expected' => $nothing,
	],

	'testShouldTrustNobodyWhenTheResponseIsNotAnObject' => [
		'config'   => [ 'response' => 'unauthorized' ],
		'expected' => $nothing,
	],

	// wp-rocket.me reachable but not yet serving the block: an older website
	// release, or the feature switched off for this pod.
	'testShouldTrustNobodyWhenTheBlockIsAbsent'         => [
		'config'   => [
			'response' => (object) [ 'data' => (object) [ 'rocket_insights_display_post_column' => true ] ],
		],
		'expected' => $nothing,
	],

	// The block present but empty, which is what wp-rocket.me sends when its
	// own environment variables are unset.
	'testShouldTrustNobodyWhenTheBlockIsEmpty'          => [
		'config'   => [
			'response' => (object) [ 'data' => (object) [ 'fleet' => (object) [] ] ],
		],
		'expected' => $nothing,
	],

	// A block whose values are not strings. A JSON array of keys, say, would
	// otherwise reach the verifier as something it has to guess about.
	'testShouldTrustNobodyWhenTheValuesAreNotStrings'   => [
		'config'   => [
			'response' => (object) [
				'data' => (object) [
					'fleet' => (object) [
						'issuer'       => [ 'grn:fleet' ],
						'jwks_url'     => null,
						'crm_issuer'   => 42,
						'crm_jwks_url' => (object) [],
					],
				],
			],
		],
		'expected' => $nothing,
	],

	// Trailing whitespace in an environment variable is the most likely way a
	// correct issuer fails to match, and it fails at the signature check where
	// nothing points at the cause.
	'testShouldTrimSurroundingWhitespace'               => [
		'config'   => [
			'response' => (object) [
				'data' => (object) [
					'fleet' => (object) [
						'issuer'       => "  {$fleet['issuer']}\n",
						'jwks_url'     => " {$fleet['jwks_url']} ",
						'crm_issuer'   => "\t{$fleet['crm_issuer']}",
						'crm_jwks_url' => "{$fleet['crm_jwks_url']}  ",
					],
				],
			],
		],
		'expected' => $everything,
	],

	// The fleet key present but not an object — a scalar or a list. Casting a
	// list to an array would give integer keys and read as "no issuer", which
	// is the right answer, but it must be the deliberate one.
	'testShouldTrustNobodyWhenTheBlockIsNotAnObject'    => [
		'config'   => [
			'response' => (object) [ 'data' => (object) [ 'fleet' => 'yes' ] ],
		],
		'expected' => $nothing,
	],
];
