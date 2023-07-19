<?php
return [
    'activatedShouldAdd' => [
        'config' => [
              'exclude_defer_js' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'wp-rocket/assets/js/lazyload-css.min.js',
        ]
    ],
	'disactivatedShouldReturnSame' => [
		'config' => [
			'exclude_defer_js' => [],
			'enabled' => false,
		],
		'expected' => [
		]
	],
];
