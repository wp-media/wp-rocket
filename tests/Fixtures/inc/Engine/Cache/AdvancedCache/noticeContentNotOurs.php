<?php

return [

	'testShouldReturnNullWhenPluginsActivate' => [
		'config'   => [
			'pagenow'                  => 'plugins.php',
			'activate'                 => true,
			'cap'                      => false,
			'valid_key'                => true,
			'wp_cache'                 => true,
			'wp_rocket_advanced_cache' => true,
			'message'                  => '',
		],
		'expected' => null,
	],

	'testShouldReturnNullWhenNoCap' => [
		'config'   => [
			'pagenow'                  => 'settings-general.php',
			'activate'                 => null,
			'cap'                      => false,
			'valid_key'                => true,
			'wp_cache'                 => true,
			'wp_rocket_advanced_cache' => true,
			'message'                  => '',
		],
		'expected' => null,
	],

	'testShouldReturnNullWhenNoValidKey' => [
		'config'   => [
			'pagenow'                  => 'settings-general.php',
			'activate'                 => null,
			'cap'                      => true,
			'valid_key'                => false,
			'wp_cache'                 => true,
			'wp_rocket_advanced_cache' => true,
			'message'                  => '',
		],
		'expected' => null,
	],

	'testShouldReturnNullWPCacheFalse' => [
		'config'   => [
			'pagenow'                  => 'settings-general.php',
			'activate'                 => null,
			'cap'                      => true,
			'valid_key'                => true,
			'wp_cache'                 => false,
			'wp_rocket_advanced_cache' => true,
			'message'                  => '',
		],
		'expected' => null,
	],

	'testShouldReturnNullWhenConstantSet' => [
		'config'   => [
			'pagenow'                  => 'settings-general.php',
			'activate'                 => null,
			'cap'                      => true,
			'valid_key'                => true,
			'wp_cache'                 => true,
			'wp_rocket_advanced_cache' => true,
			'message'                  => '',
		],
		'expected' => null,
	],

	'testShouldDisplayNotice' => [
		'config'   => [
			'pagenow'                  => 'settings-general.php',
			'activate'                 => null,
			'cap'                      => true,
			'valid_key'                => true,
			'wp_cache'                 => true,
			'wp_rocket_advanced_cache' => false,
			'message'                  => <<<HTML
<strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.
<br>Affected file/folder: <code>wp-content/advanced-cache.php</code>
<br>Troubleshoot: <a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>
HTML
			,
		],
		'expected' => <<<HTML
<div class="notice notice-error ">
	<p>
	    <strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.
	    <br>Affected file/folder: <code>wp-content/advanced-cache.php</code>
	    <br>Troubleshoot: <a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>
	</p>
</div>
HTML
		,
	],
];
