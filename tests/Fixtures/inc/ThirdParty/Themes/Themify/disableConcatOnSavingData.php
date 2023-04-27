<?php
return [
    'RucssDisabledShouldChange' => [
        'config' => [
			'rucss_enabled' => false,
			'value' => [
				'test' => true,
			]
        ],
        'expected' => [
			'test' => true,
        ]
    ],
	'RucssEnabledShouldAdd' => [
		'config' => [
			'rucss_enabled' => true,
			'value' => [
				'test' => true,
			]
		],
		'expected' => [
			'test' => true,
			'setting-dev-mode-concate' => false
		]
	],

];
