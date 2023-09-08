<?php
return [
    'OptionDisableShouldReturnSame' => [
        'config' => [
              'exclusions' => [],
			  'option' => false,
        ],
        'expected' => [

        ]
    ],
	'OptionEnabledShouldExclude' => [
		'config' => [
			'exclusions' => [],
			'option' => true,
		],
		'expected' => [
			'\/jquery(-migrate)?-?([0-9.]+)?(.min|.slim|.slim.min)?.js(\?(.*))?( |\'|"|>)'
		]
	]
];
