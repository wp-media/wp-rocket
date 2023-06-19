<?php
return [
    'RucssActivatedAndNotDisabledShouldAdd' => [
        'config' => [
			'value' => [
				'test' => true,
			],
			'need_add' => true,
			'same_rucss' => false,
			'rucss_enabled' => true,
			'has_constant' => true,
			'new_configurations' => [
				'remove_unused_css' => true,
			],
			'old_configurations' => [
				'remove_unused_css' => false,
			]
        ],
		'expected' => [
			'value' => [
				'test' => true,
				'setting-dev-mode' => true,
				'setting-dev-mode-concate' => true,
			]
		]
    ],
	'RucssDeasactivatedShouldAddDisabled' => [
		'config' => [
			'value' => [
				'test' => true,
			],
			'need_add' => false,
			'has_constant' => true,
			'same_rucss' => false,
			'new_configurations' => [
				'remove_unused_css' => false,
			],
			'old_configurations' => [
				'remove_unused_css' => true,
			]
		],
		'expected' => [
			'value' => [
				'test' => true,
				'setting-dev-mode-concate' => false,
				'setting-dev-mode' => false,
			]
		]
	],
	'RucssAlreadyDeasactivatedShouldNotAdd' => [
		'config' => [
			'value' => [
				'test' => true,
			],
			'need_add' => false,
			'has_constant' => true,
			'same_rucss' => true,
			'new_configurations' => [
				'remove_unused_css' => false,
			],
			'old_configurations' => [
				'remove_unused_css' => false,
			]
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
			'same_rucss' => false,
			'has_constant' => true,
			'new_configurations' => [
				'remove_unused_css' => true,
			],
			'old_configurations' => [
				'remove_unused_css' => false,
			]
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
