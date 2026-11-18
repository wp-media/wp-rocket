<?php

$rocket_mcp_eligible = [
	'abilities_enabled' => true,
	'caps'              => [ 'install_plugins', 'rocket_manage_options' ],
	'adapter_loaded'    => false,
	'user_meta'         => [
		'mcp_adapter_sessions' => [ serialize( [ 'session-id' => [ 'created_at' => 1 ] ] ) ], // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
	],
	'boxes'             => [ 'some_other_notice' ],
];

return [
	'test_data' => [
		'shouldReturnTrueForEligibleUserWithLegacySessions' => [
			'config'   => $rocket_mcp_eligible,
			'expected' => true,
		],
		'shouldReturnFalseWhenAbilitiesDisabled'         => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'abilities_enabled' => false ] ),
			'expected' => false,
		],
		'shouldReturnFalseWithoutInstallPluginsCap'      => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'caps' => [ 'rocket_manage_options' ] ] ),
			'expected' => false,
		],
		'shouldReturnFalseWithoutRocketManageOptionsCap' => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'caps' => [ 'install_plugins' ] ] ),
			'expected' => false,
		],
		'shouldReturnFalseWhenAdapterLoaded'             => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'adapter_loaded' => true ] ),
			'expected' => false,
		],
		'shouldReturnFalseWithoutMcpMeta'                => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'user_meta' => [ 'rocket_boxes' => [ 'a:0:{}' ] ] ] ),
			'expected' => false,
		],
		'shouldReturnFalseWithEmptySessions'             => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'user_meta' => [ 'mcp_adapter_sessions' => [ 'a:0:{}' ] ] ] ),
			'expected' => false,
		],
		'shouldReturnTrueWithBlogScopedSessions'         => [
			'config'   => array_merge(
				$rocket_mcp_eligible,
				[
					'blog_id'   => 2,
					'user_meta' => [ 'mcp_adapter_sessions_2' => [ serialize( [ 'session-id' => [ 'created_at' => 1 ] ] ) ] ], // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				]
			),
			'expected' => true,
		],
		'shouldReturnFalseWithOtherBlogScopedSessions'   => [
			'config'   => array_merge(
				$rocket_mcp_eligible,
				[
					'blog_id'   => 3,
					'user_meta' => [ 'mcp_adapter_sessions_2' => [ serialize( [ 'session-id' => [ 'created_at' => 1 ] ] ) ] ], // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
				]
			),
			'expected' => false,
		],
		'shouldReturnTrueWithOAuthRefreshJtiOnly'        => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'user_meta' => [ 'mcp_refresh_jti_abc-123' => [ 'jti' ] ] ] ),
			'expected' => true,
		],
		'shouldReturnFalseWhenDismissed'                 => [
			'config'   => array_merge( $rocket_mcp_eligible, [ 'boxes' => [ 'some_other_notice', 'mcp_adapter_notice' ] ] ),
			'expected' => false,
		],
	],
];
