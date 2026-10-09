<?php

$rocket_mcp_notice = <<<'HTML'
<div class="notice notice-info ">
	<p>WP Rocket no longer bundles the MCP Adapter, which is now available as a standalone plugin on WordPress.org. To keep using MCP with WP Rocket, please install and activate the <a href="http://example.org/wp-admin/plugin-install.php?s=mcp-adapter&#038;tab=search&#038;type=term">MCP Adapter plugin</a>.</p>
HTML;

return [
	'test_data' => [
		'shouldDisplayForAdministratorWithMcpSessions'    => [
			'role'      => 'administrator',
			'user_meta' => [ 'mcp_adapter_sessions' => [ 'session-id' => [ 'created_at' => 1 ] ] ],
			'expected'  => $rocket_mcp_notice,
		],
		'shouldDisplayForAdministratorWithOAuthClient'    => [
			'role'      => 'administrator',
			'user_meta' => [ 'mcp_refresh_jti_abc-123' => 'jti' ],
			'expected'  => $rocket_mcp_notice,
		],
		'shouldNotDisplayForAdministratorWithoutMcpUsage' => [
			'role'      => 'administrator',
			'user_meta' => [],
			'expected'  => '',
		],
		'shouldNotDisplayForEditorWithMcpSessions'        => [
			'role'      => 'editor',
			'user_meta' => [ 'mcp_adapter_sessions' => [ 'session-id' => [ 'created_at' => 1 ] ] ],
			'expected'  => '',
		],
	],
];
