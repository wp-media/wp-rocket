<?php
return [
    'ActivatedShouldAdd' => [
        'config' => [
              'js_files' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'wp-rocket/assets/js/lazyload-css.min.js',
        ]
    ],
	'DisabledShouldKeepSame' => [
		'config' => [
			'js_files' => [],
			'enabled' => false,
		],
		'expected' => [
		]
	],

];
