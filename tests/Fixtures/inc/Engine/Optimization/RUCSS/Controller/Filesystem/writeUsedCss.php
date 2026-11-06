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
		'shouldWriteUsedCss' => [
			'hash' => 'fa2965d41f1515951de523cecb81f85e',
			'file' => [
				'content' => 'css content',
				'path'    => 'vfs://public/wp-content/cache/used-css/1/f/a/2/965d41f1515951de523cecb81f85e.css.gz',
			],
		],
	],
];
