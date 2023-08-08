<?php
return [
    'valuesShouldSaveTheHash' => [
        'config' => [
              'oldvalue' => [],
              'value' => [
				  'test' => 'test'
			  ],
			'created_hash' => '828bcef8763c1bc616e25a06be4b90ff'
        ],
        'expected' => [
			'value' => null,
			'hash' => '828bcef8763c1bc616e25a06be4b90ff',
        ]
    ],
	'EmptyvaluesShouldNotSaveTheHash' => [
		'config' => [
			'oldvalue' => [],
			'value' => false,
			'created_hash' => '828bcef8763c1bc616e25a06be4b90ff'
		],
		'expected' => [
			'value' => null,
			'hash' => false,
		]
	],
];
