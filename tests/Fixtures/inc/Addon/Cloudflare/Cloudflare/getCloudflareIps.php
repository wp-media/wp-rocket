<?php

return [
	'shouldReturnTransientValue' => [
		'config' => [
			'transient' => (object) [
				'ipv4_cidrs' => [
					'173.245.48.0/20',
					'103.21.244.0/22',
					'103.22.200.0/22',
					'103.31.4.0/22',
					'141.101.64.0/18',
					'108.162.192.0/18',
					'190.93.240.0/20',
					'188.114.96.0/20',
					'197.234.240.0/22',
					'198.41.128.0/17',
					'162.158.0.0/15',
					'104.16.0.0/12',
					'104.24.0.0/14',
					'172.64.0.0/13',
					'131.0.72.0/22',
				],
				'ipv6_cidrs' => [
					'2400:cb00::/32',
					'2606:4700::/32',
					'2803:f800::/32',
					'2405:b500::/32',
					'2405:8100::/32',
					'2a06:98c0::/29',
					'2c0f:f248::/32',
				],
			],
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => (object) [
						'ipv4_cidrs' => [],
						'ipv6_cidrs' => [],
					],
				] ),
				'response' => '',
				'cookies' => [],
			],
			'wp_error' => false,
		],
		'expected' => (object) [
			'ipv4_cidrs' => [
				'173.245.48.0/20',
				'103.21.244.0/22',
				'103.22.200.0/22',
				'103.31.4.0/22',
				'141.101.64.0/18',
				'108.162.192.0/18',
				'190.93.240.0/20',
				'188.114.96.0/20',
				'197.234.240.0/22',
				'198.41.128.0/17',
				'162.158.0.0/15',
				'104.16.0.0/12',
				'104.24.0.0/14',
				'172.64.0.0/13',
				'131.0.72.0/22',
			],
			'ipv6_cidrs' => [
				'2400:cb00::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2405:b500::/32',
				'2405:8100::/32',
				'2a06:98c0::/29',
				'2c0f:f248::/32',
			],
		],
	],
	'shouldReturnDefaultValueWhenEmptyResponse' => [
		'config' => [
			'transient' => false,
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => '',
				] ),
				'response' => '',
				'cookies' => [],
			],
			'wp_error' => false,
		],
		'expected' => (object) [
			'ipv4_cidrs' => [
				'173.245.48.0/20',
				'103.21.244.0/22',
				'103.22.200.0/22',
				'103.31.4.0/22',
				'141.101.64.0/18',
				'108.162.192.0/18',
				'190.93.240.0/20',
				'188.114.96.0/20',
				'197.234.240.0/22',
				'198.41.128.0/17',
				'162.158.0.0/15',
				'104.16.0.0/12',
				'104.24.0.0/14',
				'172.64.0.0/13',
				'131.0.72.0/22',
			],
			'ipv6_cidrs' => [
				'2400:cb00::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2405:b500::/32',
				'2405:8100::/32',
				'2a06:98c0::/29',
				'2c0f:f248::/32',
			],
		],
	],
	'shouldReturnDefaultValueWhenWPError' => [
		'config' => [
			'transient' => false,
			'response' => new WP_Error( 'error' ),
			'wp_error' => true,
		],
		'expected' => (object) [
			'ipv4_cidrs' => [
				'173.245.48.0/20',
				'103.21.244.0/22',
				'103.22.200.0/22',
				'103.31.4.0/22',
				'141.101.64.0/18',
				'108.162.192.0/18',
				'190.93.240.0/20',
				'188.114.96.0/20',
				'197.234.240.0/22',
				'198.41.128.0/17',
				'162.158.0.0/15',
				'104.16.0.0/12',
				'104.24.0.0/14',
				'172.64.0.0/13',
				'131.0.72.0/22',
			],
			'ipv6_cidrs' => [
				'2400:cb00::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2405:b500::/32',
				'2405:8100::/32',
				'2a06:98c0::/29',
				'2c0f:f248::/32',
			],
		],
	],
	'shouldReturntValueWhenSuccess' => [
		'config' => [
			'transient' => false,
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => (object) [
						'ipv4_cidrs' => [
							'173.245.48.0/20',
							'103.21.244.0/22',
							'103.22.200.0/22',
							'103.31.4.0/22',
							'141.101.64.0/18',
							'108.162.192.0/18',
							'190.93.240.0/20',
							'188.114.96.0/20',
							'197.234.240.0/22',
							'198.41.128.0/17',
							'162.158.0.0/15',
							'104.16.0.0/12',
							'104.24.0.0/14',
							'172.64.0.0/13',
							'131.0.72.0/22',
						],
						'ipv6_cidrs' => [
							'2400:cb00::/32',
							'2606:4700::/32',
							'2803:f800::/32',
							'2405:b500::/32',
							'2405:8100::/32',
							'2a06:98c0::/29',
							'2c0f:f248::/32',
						],
					],
				] ),
				'response' => '',
				'cookies' => [],
			],
			'wp_error' => false,
		],
		'expected' => (object) [
			'ipv4_cidrs' => [
				'173.245.48.0/20',
				'103.21.244.0/22',
				'103.22.200.0/22',
				'103.31.4.0/22',
				'141.101.64.0/18',
				'108.162.192.0/18',
				'190.93.240.0/20',
				'188.114.96.0/20',
				'197.234.240.0/22',
				'198.41.128.0/17',
				'162.158.0.0/15',
				'104.16.0.0/12',
				'104.24.0.0/14',
				'172.64.0.0/13',
				'131.0.72.0/22',
			],
			'ipv6_cidrs' => [
				'2400:cb00::/32',
				'2606:4700::/32',
				'2803:f800::/32',
				'2405:b500::/32',
				'2405:8100::/32',
				'2a06:98c0::/29',
				'2c0f:f248::/32',
			],
		],
	],
];
