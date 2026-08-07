<?php

return [
	'shouldReturnUIByDefault'                              => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => false,
			'wp_admin'     => true,
			'doing_cron'   => false,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'UI',
		],
	],
	'shouldReturnUIWhenDoingAjax'                          => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => true,
			'wp_admin'     => true,
			'doing_cron'   => false,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'UI',
		],
	],
	'shouldReturnUnknownWhenDoingCron'                     => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => false,
			'wp_admin'     => false,
			'doing_cron'   => true,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'Unknown',
		],
	],
	'shouldReturnUnknownOnFrontendRequest'                 => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => false,
			'wp_admin'     => false,
			'doing_cron'   => false,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'Unknown',
		],
	],
	'shouldReturnRestApiWhenRestRequestToUnknownRoute'     => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/wp-rocket/v1/rocketcdn',
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnRestApiWhenBothRestRequestAndAjax'        => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => true,
			'argv'         => [],
			'rest_route'   => '/wp-rocket/v1/rocketcdn',
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnMCPWhenRestRequestToMcpNamespace'         => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/mcp/mcp-adapter-default-server',
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
	'shouldReturnMCPWhenRestRequestToWpAbilitiesNamespace' => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/wp-abilities/v1/abilities/wp-rocket/set-option/run',
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
	'shouldReturnRestApiWhenRestRouteIsEmpty'              => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '',
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnUIWhenRestRequestHasValidDashboardNonce'  => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/wp-rocket/v1/rocketcdn',
			'nonce_header' => 'valid-nonce',
			'nonce_valid'  => true,
		],
		'expected' => [
			'channel' => 'UI',
		],
	],
	'shouldReturnRestApiWhenRestRequestHasInvalidNonce'    => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/wp-rocket/v1/rocketcdn',
			'nonce_header' => 'bad-nonce',
			'nonce_valid'  => false,
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnMCPWhenMcpRouteEvenWithValidDashboardNonce' => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
			'rest_route'   => '/mcp/mcp-adapter-default-server',
			'nonce_header' => 'valid-nonce',
			'nonce_valid'  => true,
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
	'shouldReturnCLIWhenWpCliWithoutMcpServeArgs'          => [
		'config'   => [
			'wp_cli'       => true,
			'rest_request' => false,
			'doing_ajax'   => false,
			'argv'         => [ 'wp', 'cache', 'flush' ],
		],
		'expected' => [
			'channel' => 'CLI',
		],
	],
	'shouldReturnMCPWhenWpCliWithMcpAndServeInArgv'        => [
		'config'   => [
			'wp_cli'       => true,
			'rest_request' => false,
			'doing_ajax'   => false,
			'argv'         => [ 'wp', 'mcp', 'serve' ],
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
	'shouldReturnMCPWhenGlobalsWpQueryVarHasMcpRoute'      => [
		'config'   => [
			'wp_cli'                   => false,
			'rest_request'             => true,
			'doing_ajax'               => false,
			'argv'                     => [],
			'rest_route'               => '',
			'wp_query_vars_rest_route' => '/mcp/mcp-adapter-default-server',
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
	'shouldReturnMCPWhenGetParamHasMcpRoute'               => [
		'config'   => [
			'wp_cli'         => false,
			'rest_request'   => true,
			'doing_ajax'     => false,
			'argv'           => [],
			'rest_route'     => '',
			'get_rest_route' => '/mcp/mcp-adapter-default-server',
		],
		'expected' => [
			'channel' => 'MCP',
		],
	],
];
