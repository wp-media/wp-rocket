<?php

return [
	'shouldReturnUIByDefault'                           => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => false,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'UI',
		],
	],
	'shouldReturnUIWhenDoingAjax'                       => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => false,
			'doing_ajax'   => true,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'UI',
		],
	],
	'shouldReturnRestApiWhenRestRequestToUnknownRoute'   => [
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
	'shouldReturnRestApiWhenBothRestRequestAndAjax'     => [
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
	'shouldReturnMCPWhenRestRequestToMcpNamespace'      => [
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
	'shouldReturnRestApiWhenRestRouteIsEmpty'            => [
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
	'shouldReturnCLIWhenWpCliWithoutMcpServeArgs'       => [
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
	'shouldReturnMCPWhenWpCliWithMcpAndServeInArgv'     => [
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
	'shouldReturnMCPWhenGlobalsWpQueryVarHasMcpRoute'   => [
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
	'shouldReturnMCPWhenGetParamHasMcpRoute'            => [
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
