<?php
return [
    'testInitialValueShouldNotAdd' => [
        'config' => [
			'initial_value' => 0,
        ],
    ],

	'testNoInitialValueShouldAdd' => [
		'config' => [
			'initial_value' => false,
		],
	],

];
