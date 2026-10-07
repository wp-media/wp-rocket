<?php

return [
	'test_data' => [
		'shouldDisplayForAdministratorWithMcpSessions'    => [
			'role'      => 'administrator',
			'user_meta' => [ 'mcp_adapter_sessions' => [ 'session-id' => [ 'created_at' => 1 ] ] ],
			'expected'  => true,
		],
		'shouldDisplayForAdministratorWithOAuthClient'    => [
			'role'      => 'administrator',
			'user_meta' => [ 'mcp_refresh_jti_abc-123' => 'jti' ],
			'expected'  => true,
		],
		'shouldNotDisplayForAdministratorWithoutMcpUsage' => [
			'role'      => 'administrator',
			'user_meta' => [],
			'expected'  => false,
		],
		'shouldNotDisplayForEditorWithMcpSessions'        => [
			'role'      => 'editor',
			'user_meta' => [ 'mcp_adapter_sessions' => [ 'session-id' => [ 'created_at' => 1 ] ] ],
			'expected'  => false,
		],
	],
];
