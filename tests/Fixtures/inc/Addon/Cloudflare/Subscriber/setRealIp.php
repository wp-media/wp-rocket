<?php

return [
	'testShouldReturnNullWhenServerVariablesNotSet' => [
		'config' => [],
		'expected' => null,
	],
	'testShouldDoNothingWhenNoMatch' => [
		'config' => [
			'remote_addr' => '192.168.0.1',
			'connecting_ip' => '192.168.0.1',
			'result' => (object) [
				'ipv4_cidrs' => [
					'173.245.48.0/20',
				],
				'ipv6_cidrs' => [
					'2400:cb00::/32',
				],
			],
			'in_range' => false,
		],
		'expected' => '192.168.0.1',
	],
	'testShouldUpdateRemoteAddrWhenMatch' => [
		'config' => [
			'remote_addr' => '173.245.48.0/20',
			'connecting_ip' => '192.168.0.1',
			'result' => (object) [
				'ipv4_cidrs' => [
					'173.245.48.0/20',
				],
				'ipv6_cidrs' => [
					'2400:cb00::/32',
				],
			],
			'in_range' => true,
		],
		'expected' => '192.168.0.1',
	],
];
