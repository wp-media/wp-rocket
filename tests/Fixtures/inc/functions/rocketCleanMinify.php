<?php

return [
	'vfs_dir' => 'wp-content/cache/min/',

	// Test data.
	'test_data' => [
		'shouldNotCleanWhenNoExtensionsGiven'     => [
			'extensions' => '',
			'expected'   => [
				'cleaned'     => [],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'         => true,
					'vfs://public/wp-content/cache/min/2/'         => true,
					'vfs://public/wp-content/cache/min/3rd-party/' => true,
				],
			],
		],
		'shouldNotCleanWhenExtensionDoesNotExist' => [
			'extensions' => [ 'php', 'html' ],
			'expected'   => [
				'cleaned'     => [],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'         => true,
					'vfs://public/wp-content/cache/min/2/'         => true,
					'vfs://public/wp-content/cache/min/3rd-party/' => true,
				],
			],
		],
		'shouldClean_css'                         => [
			'extensions' => 'css',
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'                                       => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => false,

					'vfs://public/wp-content/cache/min/2/' => true,

					'vfs://public/wp-content/cache/min/3rd-party/'                                       => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => false,
				],
			],
		],
		'shouldClean_css.gz'                      => [
			'extensions' => 'css.gz',
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz'         => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'                                       => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'   => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => false,

					'vfs://public/wp-content/cache/min/2/' => true,

					'vfs://public/wp-content/cache/min/3rd-party/'                                       => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'   => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => false,
				],
			],
		],
		'shouldClean_js'                          => [
			'extensions' => 'js',
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'                                        => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => false,

					'vfs://public/wp-content/cache/min/2/' => true,

					'vfs://public/wp-content/cache/min/3rd-party/'                                        => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => false,
				],
			],
		],
		'shouldClean_js.gz'                       => [
			'extensions' => 'js.gz',
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'         => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz' => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'                                        => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'     => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => false,

					'vfs://public/wp-content/cache/min/2/' => true,

					'vfs://public/wp-content/cache/min/3rd-party/'                                        => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'     => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => false,
				],
			],
		],
		'shouldCleanCssAndJs'                     => [
			'extensions' => [ 'css', 'js' ],
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'     => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'  => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,

					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'     => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz'  => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'         => false,
					'vfs://public/wp-content/cache/min/2/'         => true,
					'vfs://public/wp-content/cache/min/3rd-party/' => false,
				],
			],
		],
		'shouldClean_.gz'                         => [
			'extensions' => 'gz',
			'expected'   => [
				'cleaned'     => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz'         => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz'          => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js.gz'  => null,
				],
				'non_cleaned' => [
					'vfs://public/wp-content/cache/min/1/'                                     => false,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js'  => false,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' => false,

					'vfs://public/wp-content/cache/min/2/' => true,

					'vfs://public/wp-content/cache/min/3rd-party/'                                     => false,
					'vfs://public/wp-content/cache/min/3rd-party/bt937b0e3a1884eec34a989485f863ff.js'  => false,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css' => false,
				],
			],
		],
	],
];
