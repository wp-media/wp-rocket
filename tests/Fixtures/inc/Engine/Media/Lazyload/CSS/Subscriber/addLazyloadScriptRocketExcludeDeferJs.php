<?php
return [
    'activatedShouldAdd' => [
        'config' => [
              'exclude_defer_js' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'#rocket_lazyload_css-js',
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
