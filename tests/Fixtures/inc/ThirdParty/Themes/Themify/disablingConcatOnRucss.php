<?php
return [
    'RucssActivatedAndNotDisabledShouldAdd' => [
        'config' => [
			'value' => [
				'test' => true,
			],
			'need_add' => true,
			'rucss_enabled' => true,
			'has_constant' => true,
        ],
		'expected' => [
			'value' => [
				'test' => true,
				'setting-dev-mode' => true,
				'setting-dev-mode-concate' => true,
			]
		]
    ],
	'RucssDisactivatedShouldNotAdd' => [
		'config' => [
			'value' => [
				'test' => true,
			],
			'need_add' => false,
			'rucss_enabled' => false,
			'has_constant' => true,
		],
		'expected' => [
			'value' => [
				'test' => true,
			]
		]
	],
	'RucssActivatedWithSetShouldNotAdd' => [
		'config' => [
			'value' => [
				'setting-dev-mode-concate' => true,
				'setting-dev-mode' => true,
				'test' => true,
			],
			'need_add' => false,
			'rucss_enabled' => true,
			'has_constant' => true,
		],
		'expected' => [
			'value' => [
				'setting-dev-mode-concate' => true,
				'setting-dev-mode' => true,
				'test' => true,
			]
		]
	],
];
