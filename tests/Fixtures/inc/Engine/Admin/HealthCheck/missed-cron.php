<?php

return [
	'noCap' => [
		[
			'cap'       => false,
			'screen'    => 'options_general',
			'dismissed' => [],
			'options'   => [
				0,
			],
			'events' => [
				false,
			],
			'expected' => '',
		],
	],
	'badScreen' => [
		[
			'cap'       => true,
			'screen'    => 'options_general',
			'dismissed' => [],
			'options'   => [
				0,
			],
			'events' => [
				false,
			],
			'expected' => '',
		],
	],
	'dismissedWarning' => [
		[
			'cap'       => true,
			'screen'    => 'settings_page_wprocket',
			'dismissed' => [
				'rocket_warning_cron'
			],
			'options'   => [
				0,
			],
			'events' => [
				false,
			],
			'expected' => '',
		],
	],
	'disabledOptions' => [
		[
			'cap'       => true,
			'screen'    => 'settings_page_wprocket',
			'dismissed' => [],
			'options'   => [
				0,
			],
			'events' => [
				false,
			],
			'expected' => '',
		],
	],
	'noScheduledEvents' => [
		[
			'cap'       => true,
			'screen'    => 'settings_page_wprocket',
			'dismissed' => [],
			'options'   => [
				1,
			],
			'events' => [
				false,
			],
			'expected' => '',
		],
	],
	'noMissedEvents' => [
		[
			'cap'       => true,
			'screen'    => 'settings_page_wprocket',
			'dismissed' => [],
			'options'   => [
				1,
			],
			'events' => [
				time(),
			],
			'expected' => '',
		],
	],
	'PurgeTimeMissedEvent' => [
		[
			'cap'       => true,
			'screen'    => 'settings_page_wprocket',
			'dismissed' => [],
			'options'   => [
				1,
			],
			'events' => [
				time() - 600,
				time()
			],
			'expected' => '<div class="notice notice-warning ">
			<p>The following scheduled event failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:</p>
			<ul>
			<li>Scheduled Cache Purge</li>
			</ul>
			<p>Please contact your host to check if CRON is working.</p>
			<p><a class="rocket-dismiss" href="http://example.org/admin-post.php?action=rocket_ignore&box=rocket_warning_cron">Dismiss this notice</a></p></div>',
		],
	],
];
