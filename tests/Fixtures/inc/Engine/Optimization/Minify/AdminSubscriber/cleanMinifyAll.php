<?php

return [
    'vfs_dir'   => 'wp-content/cache/min/',

	'test_data' => [
		'shouldCleanMinifyAllWithMinifyCss'             => [
            'option'    =>  [
                'minify_js' => 0,
                'minify_css' => 1,
            ],
            'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/css/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/css/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.css.gz' => null,
				],
			],
		],
        'shouldCleanMinifyAllWithMinifyJs'             => [
            'option'    =>  [
                'minify_js' => 1,
                'minify_css' => 0,
            ],
            'expected'   => [
				'cleaned' => [
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/css/'                         => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/plugins/imagify/assets/js/' => null,

					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js'    => null,
					'vfs://public/wp-content/cache/min/3rd-party/2n7x3vd41f1515951de523cecb81f85e.js.gz' => null,
				],
			],
		],
        'shouldNotCleanMinifyAll'             => [
            'option'    =>  [
                'minify_js' => 0,
                'minify_css' => 0,
            ],
            'expected'   => [
				'cleaned' => [],
			],
		],
	],
];
