<?php

return [
	'notice' => <<<HTML
        <div class="notice notice-success is-dismissible">
        <p>
        <strong>
        New in WP Rocket: Meet Rocket Insights, your built-in performance tracking tool!</strong>
        </p>
        <p>
        Starting from WP Rocket 3.20, you can track your key pages’ performance directly from your dashboard and get in-depth insights.</p>
        <p>
        🚀 Add your first page, run the test, and keep your site fast.</p>
        <p>
        <a class="button button-primary" href="http://example.org/wp-admin/options-general.php?page=wprocket#rocket_insights">
        Run the test now!</a>
        <a class="rocket-dismiss button button-primary" href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_insights_promotion_notice&amp;_wpnonce=123456">
        Dismiss this notice</a>
        </p>
        </div>
    HTML,

	'test_data' => [
        'testShouldNotDisplayNoticeWhenUrlsAreMonitored' => [
            'config'         => [
				'role' => 'administrator',
                'user_meta' => false,
                'is_monitored' => true,
			],
			'expected'       => [
				'should_display' => false,
			],
		],
		'testShouldNotDisplayNoticeWhenNoCapability' => [
            'config'         => [
				'role' => 'editor',
                'user_meta' => false,
                'is_monitored' => false,
			],
			'expected'       => [
				'should_display' => false,
			],
		],
		'testShouldNotDisplayNoticeWhenDismissed' => [
            'config'         => [
				'role'      => 'administrator',
				'user_meta' => true,
                'is_monitored' => false,
            ],
			'expected'       => [
				'should_display' => false,
			],
		],
		'testShouldDisplayNotice' => [
            'config'         => [
                'role'   => 'administrator',
                'user_meta' => false,
                'is_monitored' => false,
            ],
            'expected'       => [
                'should_display' => true,
            ],
		],
	],
];
