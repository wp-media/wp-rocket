<?php
return [
    'RUCSSEnabledShouldEnableDevMode' => [
        'config' => [
              'is_enabled' => false,
			  'rucss_enabled' => true,
        ],
        'expected' => true
    ],
	'RUCSSDisableShouldKeepDevMode' => [
		'config' => [
			'is_enabled' => false,
			'rucss_enabled' => false,
		],
		'expected' => false
	],
];
