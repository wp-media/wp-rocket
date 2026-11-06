<?php
return [
    'noKeyShouldReturnSame' => [
        'config' => [
              'args' => [],

        ],
        'expected' => [

        ]
    ],
	'wrongKeyShouldReturnSame' => [
		'config' => [
			'args' => [
				'action' => 'random'
			],
			'admin_url' => 'http://example.org/wp-admin/admin-post.php',
			'nonce' => 'nonce_url',
			'query_url' => 'nonce_url?action=rocket_regenerate_configuration',
		],
		'expected' => [
			'action' => 'random'
		]
	],
	'keyShouldAddHTMLContent' => [
		'config' => [
			'args' => [
				'action' => 'regenerate_configuration'
			],
			'admin_url' => 'http://example.org/wp-admin/admin-post.php',
			'nonce' => 'nonce_url',
			'query_url' => 'nonce_url?action=rocket_regenerate_configuration',
		],
		'expected' => [
			'action' => '<a class="wp-core-ui button" href="nonce_url?action=rocket_regenerate_configuration">Regenerate WP Rocket configuration files now</a>'
		]
	],

];
