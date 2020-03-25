<?php

return [
	'vfs_dir'   => 'wp-content/cache/min/',

	// Virtual filesystem structure.
	'structure' => [
		'wp-content' => [
			'cache' => [
				'min' => [
					'1'         => [
						'5c795b0e3a1884eec34a989485f863ff.js'     => '',
						'5c795b0e3a1884eec34a989485f863ff.js.gz'  => '',
						'fa2965d41f1515951de523cecb81f85e.css'    => '',
						'fa2965d41f1515951de523cecb81f85e.css.gz' => '',
					],
					'3rd-party' => [
						'2n7x3vd41f1515951de523cecb81f85e.css'    => '',
						'2n7x3vd41f1515951de523cecb81f85e.css.gz' => '',
						'bt937b0e3a1884eec34a989485f863ff.js'     => '',
						'bt937b0e3a1884eec34a989485f863ff.js.gz'  => '',
					],
				],
			],
		],
	],

	// Test data.
	// The virtual filesystem does not work with glob. Therefore, we have to specify all of the file extensions.
	'test_data' => [
		[
			[ 'css', 'css.gz' ],
			[
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz',
			]
		],
		[
			[ 'js', 'js.gz' ],
			[
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz',
			]
		],
		[
			[ 'css', 'css.gz', 'js', 'js.gz' ],
			[
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css',
				'wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz',
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css',
				'wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js',
				'wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz',
			]
		],
	],
];
