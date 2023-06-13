<?php
return [
    'noTransientShouldInstall' => [
        'config' => [
			'is_valid_version' => true,
			'new_version' => '3.15',
			'old_version' => '3.14',
			'has_transient' => false,
        ],
		'expected' => [
			'has_transient' => 0,
		]
    ],
	'transientShouldTransfer' => [
		'config' => [
			'is_valid_version' => true,
			'new_version' => '3.15',
			'old_version' => '3.14',
			'has_transient' => true,
		],
		'expected' => [
			'has_transient' => true,
		]
	],
    'wrongVersionShouldDoNothing' => [
		'config' => [
			'is_valid_version' => false,
			'new_version' => '3.16',
			'old_version' => '3.15',
			'has_transient' => false,
		],
		'expected' => [
			'has_transient' => false,
		]
	]
];
