<?php
return [
    'shouldDisplay' => [
        'config' => [
			'can' => true,
			'is_accessible' => false,
			'root_path' => 'root_path',
			'message' => 'message',
        ],
		'expected' => [
			'notice' => [
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => 'message',
			]
		]
    ],
	'noRightShouldDoNothing' => [
		'config' => [
			'can' => false,
			'is_accessible' => false,
			'root_path' => 'root_path',
			'message' => 'message',
		],
		'expected' => [
			'notice' => [
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => 'message',
			]
		]
	],
	'accessibleShouldDoNothing' => [
		'config' => [
			'can' => true,
			'is_accessible' => true,
			'root_path' => 'root_path',
			'message' => 'message',
		],
		'expected' => [
			'notice' => [
				'status'           => 'error',
				'dismissible'      => '',
				'message'          => 'message',
			]
		]
	]
];
