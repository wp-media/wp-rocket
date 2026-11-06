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
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/admin-bar-924d9d45c4af91c09efb7ad055662025.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/admin-bar-bce302f71910a4a126f7df01494bd6e0.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/admin-bar-171a2ef75c22c390780fe898f9d40c8d.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/admin-bar-e4aa3c9df56ff024286f4df600f4c643.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/admin-bar-85585a650224ba853d308137b9a13487.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/dashicons-c2ba5f948753896932695bf9dad93d5e.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/jquery/jquery-migrate-ca635e318ab90a01a61933468e5a72de.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/admin-bar-65d8267e813dff6d0059914a4bc252aa.js' => null,
					'vfs://public/wp-content/cache/min/3rd-party/' => [],
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
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js' => null,
					'vfs://public/wp-content/cache/min/1/5c795b0e3a1884eec34a989485f863ff.js.gz' => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css' => null,
					'vfs://public/wp-content/cache/min/1/fa2965d41f1515951de523cecb81f85e.css.gz' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/admin-bar-924d9d45c4af91c09efb7ad055662025.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/css/admin-bar-bce302f71910a4a126f7df01494bd6e0.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/admin-bar-171a2ef75c22c390780fe898f9d40c8d.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-content/plugins/imagify/assets/js/admin-bar-e4aa3c9df56ff024286f4df600f4c643.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/admin-bar-85585a650224ba853d308137b9a13487.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/css/dashicons-c2ba5f948753896932695bf9dad93d5e.css' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/jquery/jquery-migrate-ca635e318ab90a01a61933468e5a72de.js' => null,
					'vfs://public/wp-content/cache/min/1/wp-includes/js/admin-bar-65d8267e813dff6d0059914a4bc252aa.js' => null,
					'vfs://public/wp-content/cache/min/3rd-party/' => [],
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
