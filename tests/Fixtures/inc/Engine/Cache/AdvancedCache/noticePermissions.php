<?php

$content = require WP_ROCKET_TESTS_FIXTURES_DIR . '/content/advancedCacheContent.php';

return [
    'vfs_dir' => 'public/',
    'test_data' => [
        'testShouldReturnNullWhenNoCap' => [
            'config' => [
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
            'config' => [
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
            'config' => [
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
            'config' => [
                'cap'       => true,
                'valid_key' => true,
                'writable'  => false,
                'constant'  => true,
                'boxes'     => [],
                'message'   => '',
            ],
            'expected' => null,
        ],
        'testShouldReturnNullWhenDismissed' => [
            'config' => [
                'cap'       => true,
                'valid_key' => true,
                'writable'  => false,
                'constant'  => false,
                'boxes'     => [
                    'rocket_warning_advanced_cache_permissions',
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
                'message'   => '<strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.
                <br>Affected file/folder: <code>wp-content/advanced-cache.php</code>
                <br>Troubleshoot: <a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>',
            ],
            'expected' => '<div class="notice notice-error ">
            <p>
                <strong>WP Rocket</strong> cannot configure itself due to missing writing permissions.
                <br>Affected file/folder: <code>wp-content/advanced-cache.php</code>
                <br>Troubleshoot: <a href="https://docs.wp-rocket.me/article/626-how-to-make-system-files-htaccess-wp-config-writeable/?utm_source=wp_plugin&utm_medium=wp_rocket" target="_blank">How to make system files writeable</a>
            </p>
            <p>The following code should have been written to this file:
			<br><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6">' . $content['non_mobile'] . '</textarea>
		    </p>
            <p>
                <a class="rocket-dismiss" href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&box=rocket_warning_advanced_cache_permissions&_wpnonce=123456">Dismiss this notice</a>
            </p>
            </div>',
        ],
    ],
];