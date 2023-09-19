<?php

$notice = <<<NOTICE
<div class="notice notice-error ">
<p>
<strong>
WP Rocket</strong>
cannot configure itself due to missing writing permissions.<br>
Affected file/folder:<code>
vfs://public/wp-content/cache/background-css/</code>
<br>
Troubleshoot:<a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">
How to make system files writeable</a>
</p>
</div>
NOTICE;


return [
	'vfs_dir'   => 'wp-content/',
	'structure' => [
		'wp-content' => [
			'cache' => [
				'background-css' => [

				]
			]
		]
	],
	'test_data' => [
		'noPermissionShouldDisplayNothing' => [
			'config' => [
				'can' => false,
				'writable' => false
			],
			'expected' => [
				'content' => $notice,
				'contains' => false
			]
		],
		'writableShouldDisplayNoting' => [
			'config' => [
				'can' => true,
				'writable' => true
			],
			'expected' => [
				'content' => $notice,
				'contains' => false
			]
		],
		'notWritableShouldDisplayNotice' => [
			'config' => [
				'can' => true,
				'writable' => false
			],
			'expected' => [
				'content' => $notice,
				'contains' => true
			]
		]
	]
];
