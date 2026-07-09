<?php

$default_trusted_publishers = [
	'claude' => [
		'client_ids' => [
			'https://claude.ai/oauth/claude-code-client-metadata',
			'https://claude.ai/oauth/mcp-oauth-client-metadata',
		],
		'host'       => 'claude.ai',
	],
];

$filtered_trusted_publishers         = $default_trusted_publishers;
$filtered_trusted_publishers['acme'] = [
	'client_ids' => [
		'https://acme.example/oauth/client-metadata',
	],
	'host'       => 'acme.example',
];

return [
	'test_data' => [
		'shouldVerifyDefaultClaudePublisherWhenNoFilterListener' => [
			'trusted_publishers' => $default_trusted_publishers,
			'client_id'          => 'https://claude.ai/oauth/claude-code-client-metadata',
			'expected'           => [
				'verified'  => true,
				'publisher' => 'claude',
			],
		],
		'shouldNotVerifyUnlistedClientIdWhenNoFilterListener'    => [
			'trusted_publishers' => $default_trusted_publishers,
			'client_id'          => 'https://example.com/oauth/client-metadata',
			'expected'           => [
				'verified'  => false,
				'publisher' => '',
			],
		],
		'shouldVerifyPublisherAddedViaFilter'                    => [
			'trusted_publishers' => $filtered_trusted_publishers,
			'client_id'          => 'https://acme.example/oauth/client-metadata',
			'expected'           => [
				'verified'  => true,
				'publisher' => 'acme',
			],
		],
		'shouldNotVerifyClientIdNotPresentInFilteredList'        => [
			'trusted_publishers' => $filtered_trusted_publishers,
			'client_id'          => 'https://not-trusted.example/oauth/client-metadata',
			'expected'           => [
				'verified'  => false,
				'publisher' => '',
			],
		],
	],
];
