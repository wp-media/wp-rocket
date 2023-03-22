<?php

return [
	'shouldReturnArrayWhenMinifyOff' => [
		'config' => [
			'zone_id' => '12345',
			'response' => [
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
		],
		'expected' => [
			'cache_level'       => 'aggressive',
			'minify'            => 'on',
			'rocket_loader'     => 'off',
			'browser_cache_ttl' => 14400,
		],
	],
	'shouldReturnWPErrorWhenException' => [
		'config' => [
			'zone_id' => '12345',
			'response' => 'exception',
			'action_value' => 'cache_everything',
		],
		'expected' => 'error',
	],
];
