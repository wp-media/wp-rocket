<?php
return [
    'shouldSwitchRUCSS' => [
        'config' => [
			'user_can' => true,
			'referer' => 'referer',
			'valid_value' => true,
			'get' => [
				'value' => 'true'
			],
			'options' => 'options',
        ],
		'expected' => [
			'referer' => 'referer',
			'action' => 'rucss_switch',
			'options' => 'options',
		]
     ],
	'userCannotShouldBailOut' => [
		'config' => [
			'user_can' => false,
			'referer' => 'referer',
			'valid_value' => true,
			'get' => [
				'value' => 'true'
			],
			'options' => 'options',
		],
		'expected' => [
			'referer' => 'referer',
			'action' => 'rucss_switch',
			'options' => 'options',
		]
	],
	'noValueShouldDismiss' => [
		'config' => [
			'user_can' => true,
			'referer' => 'referer',
			'valid_value' => false,
			'get' => [

			],
			'options' => 'options',
		],
		'expected' => [
			'referer' => 'referer',
			'action' => 'rucss_switch',
			'options' => 'options',
		]
	]
];
