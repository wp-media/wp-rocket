<?php

return [
	'noCap' => [
		'config'   => [
			'cap'          => false,
			'screen'       => 'options_general',
			'dismissed'    => [],
			'options'      => [
				'purge_cron'                 => 0,
				'async_css'                  => 0,
				'manual_preload'             => 0,
				'schedule_automatic_cleanup' => 0,
			],
			'events'       => [
				'rocket_purge_time_event'                      => false,
				'rocket_database_optimization_time_event'      => false,
				'rocket_database_optimization_cron_interval'   => false,
				'rocket_preload_cron_interval'                 => false,
				'rocket_critical_css_generation_cron_interval' => false,
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
				'purge_cron'                 => 0,
				'async_css'                  => 0,
				'manual_preload'             => 0,
				'schedule_automatic_cleanup' => 0,
			],
			'events'       => [
				'rocket_purge_time_event'                      => false,
				'rocket_database_optimization_time_event'      => false,
				'rocket_database_optimization_cron_interval'   => false,
				'rocket_preload_cron_interval'                 => false,
				'rocket_critical_css_generation_cron_interval' => false,
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
				'purge_cron'                 => 0,
				'async_css'                  => 0,
				'manual_preload'             => 0,
				'schedule_automatic_cleanup' => 0,
			],
			'events'       => [
				'rocket_purge_time_event'                      => false,
				'rocket_database_optimization_time_event'      => false,
				'rocket_database_optimization_cron_interval'   => false,
				'rocket_preload_cron_interval'                 => false,
				'rocket_critical_css_generation_cron_interval' => false,
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
				'purge_cron'                 => 0,
				'async_css'                  => 0,
				'manual_preload'             => 0,
				'schedule_automatic_cleanup' => 0,
			],
			'events'       => [
				'rocket_purge_time_event'                      => false,
				'rocket_database_optimization_time_event'      => false,
				'rocket_database_optimization_cron_interval'   => false,
				'rocket_preload_cron_interval'                 => false,
				'rocket_critical_css_generation_cron_interval' => false,
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'events'       => [
				'rocket_purge_time_event'                      => false,
				'rocket_database_optimization_time_event'      => false,
				'rocket_database_optimization_cron_interval'   => false,
				'rocket_preload_cron_interval'                 => false,
				'rocket_critical_css_generation_cron_interval' => false,
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'disable_cron' => false,
			'events'       => [
				'rocket_purge_time_event'                      => time(),
				'rocket_database_optimization_time_event'      => time(),
				'rocket_database_optimization_cron_interval'   => time(),
				'rocket_preload_cron_interval'                 => time(),
				'rocket_critical_css_generation_cron_interval' => time(),
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'events'       => [
				'rocket_purge_time_event'                      => time() - 3600,
				'rocket_database_optimization_time_event'      => time(),
				'rocket_database_optimization_cron_interval'   => time(),
				'rocket_preload_cron_interval'                 => time(),
				'rocket_critical_css_generation_cron_interval' => time(),
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'events'       => [
				'rocket_purge_time_event'                      => time() - 3600,
				'rocket_database_optimization_time_event'      => time() - 3600,
				'rocket_database_optimization_cron_interval'   => time(),
				'rocket_preload_cron_interval'                 => time(),
				'rocket_critical_css_generation_cron_interval' => time(),
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'events'       => [
				'rocket_purge_time_event'                      => time() - 7200,
				'rocket_database_optimization_time_event'      => time(),
				'rocket_database_optimization_cron_interval'   => time(),
				'rocket_preload_cron_interval'                 => time(),
				'rocket_critical_css_generation_cron_interval' => time(),
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
				'purge_cron'                 => 1,
				'async_css'                  => 1,
				'manual_preload'             => 1,
				'schedule_automatic_cleanup' => 1,
			],
			'events'       => [
				'rocket_purge_time_event'                      => time() - 7200,
				'rocket_database_optimization_time_event'      => time() - 7200,
				'rocket_database_optimization_cron_interval'   => time(),
				'rocket_preload_cron_interval'                 => time(),
				'rocket_critical_css_generation_cron_interval' => time(),
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
