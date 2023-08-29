<?php
return [
    'ActivatedShouldAdd' => [
        'config' => [
              'js_files' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'#rocket_lazyload_css-js',
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
