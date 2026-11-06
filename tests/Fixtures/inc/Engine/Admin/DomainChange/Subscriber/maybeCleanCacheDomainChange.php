<?php
return [
    'NoChangeShouldNotFireAction' => [
        'config' => [
			'rocket_configurations_changed' => false,
			'options' => [
				'test' => true
			]
        ],
		'expected' => false
    ],
	'NoOptionsShouldNotFireAction' => [
		'config' => [
			'rocket_configurations_changed' => true,
			'options' => false
		],
		'expected' => false
	],
	'OptionsAndChangeShouldFireAction' => [
		'config' => [
			'rocket_configurations_changed' => true,
			'options' => [
				'test' => true
			]
		],
		'expected' => true
	],

];
