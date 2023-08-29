<?php
return [
    'addExclusion' => [
        'config' => [
              'js_files' => [],
			  'enabled' => true,
        ],
        'expected' => [
			'#rocket_lazyload_css-js',
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
