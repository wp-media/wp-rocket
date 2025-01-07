<?php
return [
    'shouldRegister' => [
        'config' => [
			'scheduled' => false,
			'rucss' => true,
        ],
		'expected' => true
    ],
	'alreadyPresentShouldNotRegister' => [
		'config' => [
			'scheduled' => true,
			'rucss' => true,
		],
		'expected' => true
	],
	'alreadyPresentAndDisabledShouldRemove' => [
		'config' => [
			'scheduled' => true,
			'rucss' => false,
		],
		'expected' => false
	],
];
