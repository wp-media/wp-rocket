<?php

return [
	'vfs_dir' => 'public/',

	'test_data' => [

		'testShouldReturnNullWhenNoCap' => [
			'config'   => [
				'cap'       => false,
				'valid_key' => true,
				'writable'  => true,
				'constant'  => true,
				'boxes'     => [],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldReturnNullWhenNoValidKey' => [
			'config'   => [
				'cap'       => true,
				'valid_key' => false,
				'writable'  => true,
				'constant'  => true,
				'boxes'     => [],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldReturnNullWhenWritable' => [
			'config'   => [
				'cap'       => true,
				'valid_key' => true,
				'writable'  => true,
				'constant'  => false,
				'boxes'     => [],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldReturnNullWhenConstantSet' => [
			'config'   => [
				'cap'       => true,
				'valid_key' => true,
				'writable'  => false,
				'constant'  => true,
				'boxes'     => [],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldReturnNullWhenFilterFalse' => [
			'config'   => [
				'cap'       => true,
				'valid_key' => true,
				'writable'  => true,
				'constant'  => false,
				'filter'    => false,
				'boxes'     => [],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldReturnNullWhenDismissed' => [
			'config'   => [
				'cap'       => true,
				'valid_key' => true,
				'writable'  => false,
				'constant'  => false,
				'boxes'     => [
					'rocket_warning_wp_config_permissions',
				],
				'message'   => '',
			],
			'expected' => null,
		],

		'testShouldDisplayNotice' => [
			'config' => [
				'cap'       => true,
				'valid_key' => true,
				'writable'  => false,
				'constant'  => false,
				'boxes'     => [],
				'message'   => <<<HTML
<strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.
<br>Affected file/folder: <code>wp-config.php</code>
<br>Troubleshoot: <a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>
HTML
				,
			],

			'expected' => <<<HTML
<div class="notice notice-error ">
    <p>
		<strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.<br>Affected file/folder:<code> wp-config.php</code>
		<br>Troubleshoot:<a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>
	</p>
	<p>The following code should have been written to this file:<br>
	<textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6">define( &#039;WP_CACHE&#039;, true ); // Added by WP Rocket</textarea>
	</p>
	<p>
		<a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_wp_config_permissions&amp;_wpnonce=123456">Dismiss this notice</a>
	</p>
</div>
HTML
			,
		],
	],
];
