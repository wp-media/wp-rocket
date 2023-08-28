<?php
return [
    'addExclusion' => [
        'config' => [
              'js_files' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'wp-rocket/assets/js/lazyload-css.min.js',
        ]
    ],
	'disabledShouldReturnSame' => [
		'config' => [
			'js_files' => [],
			'enabled' => false,
		],
		'expected' => []
	]

];
