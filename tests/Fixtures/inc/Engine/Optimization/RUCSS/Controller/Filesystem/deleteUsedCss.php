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
		'shouldReturnDeleteFile' => [
			'hash' => '5c795b0e3a1884eec34a989485f863ff',
			'file' => 'vfs://public/wp-content/cache/used-css/1/5/c/7/95b0e3a1884eec34a989485f863ff.css.gz',
		],
	],
];
