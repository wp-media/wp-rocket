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
			'has_box' => true,
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
			'has_box' => false,
		],
		'expected' => [
			'referer' => 'referer',
			'action' => 'rucss_switch',
			'options' => 'options',
		]
	],
];
