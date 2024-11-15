<?php
return [
	'structure' => [
		'public' => [
			'wp-content' => [
				'cache' => [
					'wp-rocket' => [
						'fonts' => [
							'1' => [
								'google-font' => [
									'1' => [
										'5' => [
											'9' => [
												'5' => [
													'cb6ccb56826a802ed411cef875f0e.css',
													'cb6ccb56826a802ed411cef875f0es' => [
														'opensans' => [
															'v18' => 'mem8YaGs126MiZpBA-UFUK0Zdc0.woff2'
														]
													]
												]
											]
										],
									],
								],
							],
						],
					]
				],
			],
		],
	],
	'test_data' => [
		'shouldWriteFontCss' => [
			'config' => [
				'url'              => 'https://fonts.googleapis.com/css?family=Open+Sans',
				'css_content'      => 'url(https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFUK0Zdc0.woff2);',
				'provider'         => 'google-font',
				'local_url'        => 'http://example.org/wp-content',
				'response_code'    => 200,
				'response' => [
					'headers' => [],
					'body' => json_encode( (object) [
						'success' => true,
						'result' => [],
					] ),
					'response' => [],
				],
			],
			'expected' => [
				'path'    => 'vfs://public/wp-content/cache/wp-rocket/font/1/google-font/1/5/9/5/cb6ccb56826a802ed411cef875f0e.css',
			]
		],
	]
];
