<?php
return [
    'shouldAdd' => [
        'config' => [
			'args' => [
				'action' => 'elementor_clear_usedcss'
			],
			'request_uri' => '',
			'admin_url' => '/admin.php',
			'rucss' => true,
        ],
        'expected' => [
			'action' => '<a class="wp-core-ui button" href="/admin.php">Clear Used CSS</a>'
        ]
    ],
	'shouldAddRUCSSDisabled' => [
		'config' => [
			'args' => [
				'action' => 'elementor_clear_usedcss'
			],
			'request_uri' => '',
			'admin_url' => '/admin.php',
			'rucss' => false,
		],
		'expected' => [
			'action' => '<a class="wp-core-ui button" href="/admin.php">Clear cache</a>'
		]
	],
	'wrongActionShouldReturnSame' => [
		'config' => [
			'args' => [
				'action' => 'wrong'
			],
			'request_uri' => '',
			'admin_url' => '/admin.php',
			'rucss' => true,
		],
		'expected' => [
			'action' => 'wrong'
		]
	]

];
