<?php
return [
    'addonEnabledShouldAddSucuri' => [
        'config' => [
              'addons' => [],
			  'is_enabled' => true,
        ],
        'expected' => [
			'Sucuri'
        ]
    ],
	'addonDisabledShouldReturnSame' => [
		'config' => [
			'addons' => [],
			'is_enabled' => false,
		],
		'expected' => [

		]
	],
];
