<?php

return [
	'structure' => [
		'wp-content' => [
			'cache' => [
				'fonts' => [
					'1' => [
						'google-fonts' => [
							'fonts' => [
								's' => [
									'lato' => [
										'v24' => [
											'S6uyw4BMUTPHjx4wXg.woff2' => '',
										],
									],
									'montserrat' => [
										'v40' => [
											'memSYaGs126MiZpBA-UvWbX2vVnXBbObj2OVZyOOSr4dVJWUgsjZ0B4gaVI.woff2' => '',
										],
									],
									'oswald' => [
										'v53' => [
											'TK3_WkUHHAIjg75cFRf3bXL8LICs1_FvsUZiZQ.woff2' => '',
										],
									],
									'roboto' => [
										'v32' => [
											'KFOmCnqEu92Fr1Mu4mxK.woff2' => '',
										],
									],
								],
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'shouldDoNothingWhenOptionIsDisabled' => [
			'config' => [
				'options' => [
					'host_fonts_locally' => 0,
					'analytics_enabled'   => 1,
				],
				'transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenAnalyticsDisabled' => [
			'config' => [
				'options' => [
					'host_fonts_locally' => 1,
					'analytics_enabled'   => 0,
				],
				'transient' => false,
			],
			'expected' => false,
		],
		'shouldDoNothingWhenDataAlreadyExists' => [
			'config' => [
				'options' => [
					'host_fonts_locally' => 1,
					'analytics_enabled'  => 1,
				],
				'transient' => [
					'fonts_total_number' => 4,
					'fonts_total_size'   => '1.2 MB',
				],
			],
			'expected' => false,
		],
		'shouldCollectData' => [
			'config' => [
				'options' => [
					'host_fonts_locally' => 1,
					'analytics_enabled'   => 1,
				],
				'transient' => false,
			],
			'expected' => [
				'fonts_total_number' => 4,
				'fonts_total_size'   => '1.2 MB',
			],
		],
	],
];
