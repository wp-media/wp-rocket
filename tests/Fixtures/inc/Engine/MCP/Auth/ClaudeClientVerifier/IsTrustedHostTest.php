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

$filtered_trusted_publishers            = $default_trusted_publishers;
$filtered_trusted_publishers['acme']    = [
	'client_ids' => [
		'https://acme.example/oauth/client-metadata',
	],
	'host'       => 'acme.example',
];

return [
	'test_data' => [
		'shouldTrustDefaultClaudeHostWhenNoFilterListener'   => [
			'trusted_publishers' => $default_trusted_publishers,
			'host'               => 'https://claude.ai/oauth/claude-code-client-metadata',
			'expected'           => true,
		],
		'shouldNotTrustUnlistedHostWhenNoFilterListener'     => [
			'trusted_publishers' => $default_trusted_publishers,
			'host'               => 'https://example.com/oauth/client-metadata',
			'expected'           => false,
		],
		'shouldTrustHostAddedViaFilter'                      => [
			'trusted_publishers' => $filtered_trusted_publishers,
			'host'               => 'https://acme.example/oauth/client-metadata',
			'expected'           => true,
		],
		'shouldNotTrustHostNotPresentInFilteredList'         => [
			'trusted_publishers' => $filtered_trusted_publishers,
			'host'               => 'https://not-trusted.example/oauth/client-metadata',
			'expected'           => false,
		],
	],
];
