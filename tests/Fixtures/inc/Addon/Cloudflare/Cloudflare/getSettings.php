<?php

return [
	'shouldReturnArrayWhenMinifyOff' => [
		'config' => [
			'zone_id' => '12345',
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => [
						(object) [
							'id' =>'browser_cache_ttl',
							'value'=> 14400 ,
						],
						(object) [
							'id' =>'cache_level',
							'value'=> 'aggressive',
						],
						(object) [
							'id' =>'rocket_loader',
							'value'=> 'off',
						],
						(object) [
							'id' =>'minify',
							'value'=> (object) [
								'js' => 'off',
								'css' => 'off',
								'html' => 'off',
							],
						],
					],
				] ),
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'cache_level'       => 'aggressive',
			'minify'            => 'off',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => 14400,
		],
	],
	'shouldReturnArrayWhenMinifyOn' => [
		'config' => [
			'zone_id' => '12345',
			'response' => [
				'headers' => [],
				'body' => json_encode( (object) [
					'success' => true,
					'result' => [
						(object) [
							'id' =>'browser_cache_ttl',
							'value'=> 14400 ,
						],
						(object) [
							'id' =>'cache_level',
							'value'=> 'aggressive',
						],
						(object) [
							'id' =>'rocket_loader',
							'value'=> 'off',
						],
						(object) [
							'id' =>'minify',
							'value'=> (object) [
								'js' => 'on',
								'css' => 'on',
								'html' => 'off',
							],
						],
					],
				] ),
				'cookies' => [],
			],
			'request_error' => false,
		],
		'expected' => [
			'cache_level'       => 'aggressive',
			'minify'            => 'on',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => 14400,
		],
	],
	'shouldReturnWPErrorWhenError' => [
		'config' => [
			'zone_id' => '12345',
			'response' => new WP_Error( 'error' ),
			'request_error' => true,
		],
		'expected' => 'error',
	],
];
