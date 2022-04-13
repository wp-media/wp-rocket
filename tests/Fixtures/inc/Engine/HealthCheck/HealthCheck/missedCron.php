<?php

return [
	'noCap' => [
		'config'   => [
			'cap'          => false,
			'screen'       => 'options_general',
			'dismissed'    => [],
			'options'      => [
				0,
				0,
				0,
				0,
			],
			'events'       => [
				false,
				false,
				false,
				false,
				false,
			],
			'disable_cron' => false,
		],
		'expected' => '',
	],

	'badScreen' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'options_general',
			'dismissed'    => [],
			'options'      => [
				0,
				0,
				0,
				0,
			],
			'events'       => [
				false,
				false,
				false,
				false,
				false,
			],
			'disable_cron' => false,
		],
		'expected' => '',
	],

	'dismissedWarning' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [
				'rocket_warning_cron',
			],
			'options'      => [
				0,
				0,
				0,
				0,
			],
			'events'       => [
				false,
				false,
				false,
				false,
				false,
			],
			'disable_cron' => false,
		],
		'expected' => '',
	],

	'disabledOptions'   => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				0,
				0,
				0,
				0,
			],
			'events'       => [
				false,
				false,
				false,
				false,
				false,
			],
			'disable_cron' => false,
		],
		'expected' => '',
	],
	'noScheduledEvents' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'events'       => [
				false,
				false,
				false,
				false,
				false,
			],
			'disable_cron' => false,
		],
		'expected' => '',
	],
	'noMissedEvents'    => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'disable_cron' => false,
			'events'       => [
				time(),
				time(),
				time(),
				time(),
				time(),
			],
		],
		'expected' => '',
	],

	'PurgeTimeMissedEvent' => [
		[
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'events'       => [
				time() - 3600,
				time(),
				time(),
				time(),
				time(),
			],
			'disable_cron' => false,
		],
		'expected' => <<<HTML
<div class="notice notice-warning ">
	<p>The following scheduled event failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:</p>
	<ul>
		<li>Scheduled Cache Purge</li>
	</ul>
	<p>Please contact your host to check if CRON is working.</p>	<p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">Dismiss this notice</a></p>
</div>
HTML
		,
	],

	'MultipleMissedEvents' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'events'       => [
				time() - 3600,
				time() - 3600,
				time(),
				time(),
				time(),
			],
			'disable_cron' => false,
		],
		'expected' => <<<HTML
<div class="notice notice-warning ">
<p>The following scheduled events failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:</p>
	<ul>
		<li>Scheduled Cache Purge</li>
		<li>Scheduled Database Optimization</li>
	</ul>
	<p>Please contact your host to check if CRON is working.</p>
	<p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">Dismiss this notice</a></p>
</div>
HTML
		,
	],

	'PurgeTimeMissedEventDisabledCron' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'events'       => [
				time() - 7200,
				time(),
				time(),
				time(),
				time(),
			],
			'disable_cron' => true,
		],
		'expected' => <<<HTML
<div class="notice notice-warning ">
	<p>The following scheduled event failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:</p>
	<ul>
		<li>Scheduled Cache Purge</li>
	</ul>
	<p>Please contact your host to check if CRON is working.</p>
	<p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">Dismiss this notice</a></p>
</div>
HTML
		,
	],

	'MultipleMissedEventsDisabledCron' => [
		'config'   => [
			'cap'          => true,
			'screen'       => 'settings_page_wprocket',
			'dismissed'    => [],
			'options'      => [
				1,
				1,
				1,
				1,
			],
			'events'       => [
				time() - 7200,
				time() - 7200,
				time(),
				time(),
				time(),
			],
			'disable_cron' => true,
		],
		'expected' => <<<HTML
<div class="notice notice-warning ">
	<p>The following scheduled events failed to run. This may indicate the CRON system is not running properly, which can prevent some WP Rocket features from working as intended:</p>
	<ul>
		<li>Scheduled Cache Purge</li>
		<li>Scheduled Database Optimization</li>
	</ul>
	<p>Please contact your host to check if CRON is working.</p>
	<p><a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=rocket_warning_cron&amp;_wpnonce=123456">Dismiss this notice</a></p>
</div>
HTML
	,
	],
];
