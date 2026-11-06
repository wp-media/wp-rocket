<?php
return [
	'structure' => [
		'wp-content' => [
			'cache' => [
				'used-css' => [
					'1' => [
						'5' => [
							'c' => [
								'7' => [
									'95b0e3a1884eec34a989485f863ff.css.gz' => gzencode( 'css content' ),
								],
							],
						],
					],
				],
			],
		],
	],
	'test_data' => [
		'shouldReturnEmptyWhenFileNotExists' => [
			'hash' => 'fa2965d41f1515951de523cecb81f85e',
			'expected' => ''
		],
		'shouldReturnDataWhenFileExists' => [
			'hash' => '5c795b0e3a1884eec34a989485f863ff',
			'expected' => 'css content'
		],
	],
];
