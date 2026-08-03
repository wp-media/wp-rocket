<?php

return [
	'shouldReturnUIByDefault'                        => [
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
	'shouldReturnUIWhenDoingAjax'                    => [
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
	'shouldReturnRestApiWhenRestRequestIsSet'         => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => false,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnRestApiWhenBothRestRequestAndAjax'  => [
		'config'   => [
			'wp_cli'       => false,
			'rest_request' => true,
			'doing_ajax'   => true,
			'argv'         => [],
		],
		'expected' => [
			'channel' => 'REST API',
		],
	],
	'shouldReturnCLIWhenWpCliWithoutMcpServeArgs'    => [
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
	'shouldReturnMCPWhenWpCliWithMcpAndServeInArgv' => [
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
];
