<?php

declare( strict_types=1 );

$expected_html = <<<HTML
<div class="notice notice-info ">
<p>
<strong>WP Rocket: </strong>
We have detected that Autoptimize's JavaScript Aggregation feature is enabled. WP Rocket's Delay JavaScript Execution will not be applied to the file it creates. We suggest disabling <strong>JavaScript Aggregation</strong> to take full advantage of Delay JavaScript Execution.</p><p>
<a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=warn_when_js_aggregation_and_delay_js_active&amp;_wpnonce=123456">
Dismiss this notice</a></p></div>
HTML;

return [
	'shouldAddNoticeWhenAutoptimizeAggregateJsOnAndDelayJsActivated' => [
		'config'   => [
			'delayJSActive'                => true,
			'autoptimizeAggregateJSActive' => 'on',
			'dismissed'                    => false,
		],
		'expected' => $expected_html
	],

	'shouldSkipWhenNotOnWPRDashboardPage' => [
		'config' => [
			'delayJSActive'                => true,
			'autoptimizeAggregateJSActive' => 'on',
			'dismissed'                    => false,
			'notWPRDashboard'              => true,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsNotActivated' => [
		'config'   => [
			'delayJSActive'                => false,
			'autoptimizeAggregateJSActive' => 'off',
			'dismissed'                    => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateJsOffAndDelayJsActivated' => [
		'config'   => [
			'delayJSActive'                => true,
			'autoptimizeAggregateJSActive' => 'off',
			'dismissed'                    => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateJsOnAndDelayJsNotActivated' => [
		'config'   => [
			'delayJSActive'                => false,
			'autoptimizeAggregateJSActive' => 'on',
			'dismissed'                    => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenUserHasDismissedNotice' => [
		'config'   => [
			'delayJSActive'                => true,
			'autoptimizeAggregateJSActive' => 'on',
			'dismissed'                    => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesDelayJS' => [
		'config'   => [
			'delayJSActive'                => false,
			'autoptimizeAggregateJSActive' => 'on',
			'dismissed'                    => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesDelayJS' => [
		'config'   => [
			'delayJSActive'                => true,
			'autoptimizeAggregateJSActive' => 'off',
			'dismissed'                    => true,
		],
		'expected' => '',
	],

];
