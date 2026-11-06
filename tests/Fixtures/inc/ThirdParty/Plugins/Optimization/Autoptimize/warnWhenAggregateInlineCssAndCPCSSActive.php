<?php

declare( strict_types=1 );

$expected_html = <<<HTML
<div class="notice notice-info ">
<p><strong>WP Rocket: </strong>
We have detected that Autoptimize's Aggregate Inline CSS feature is enabled. WP Rocket's Load CSS Asynchronously will not work correctly. We suggest disabling <strong>Aggregate Inline CSS</strong> to take full advantage of Load CSS Asynchronously Execution.
</p><p>
<a class="rocket-dismiss " href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=warn_when_aggregate_inline_css_and_cpcss_active&amp;_wpnonce=123456">
Dismiss this notice</a></p></div>
HTML;

return [
	'shouldAddNoticeWhenAutoptimizeAggregateInlineCssOnAndCPCSSActivated' => [
		'config'   => [
			'cpcssActive'                         => true,
			'autoptimizeAggregateInlineCSSActive' => 'on',
			'dismissed'                           => false,
		],
		'expected' => $expected_html
	],

	'shouldSkipWhenNotOnWPRDashboardPage' => [
		'config' => [
			'cpcssActive'                         => true,
			'autoptimizeAggregateInlineCSSActive' => 'on',
			'dismissed'                           => false,
			'notWPRDashboard'                     => true,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateInlineCssOffAndCPCSSNotActivated' => [
		'config'   => [
			'cpcssActive'                         => false,
			'autoptimizeAggregateInlineCSSActive' => 'off',
			'dismissed'                           => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateInlineCssOffAndCPCSSActivated' => [
		'config'   => [
			'cpcssActive'                         => true,
			'autoptimizeAggregateInlineCSSActive' => 'off',
			'dismissed'                           => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAggregateInlineCssOnAndCPCSSNotActivated' => [
		'config'   => [
			'cpcssActive'                         => false,
			'autoptimizeAggregateInlineCSSActive' => 'on',
			'dismissed'                           => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenUserHasDismissedNotice' => [
		'config'   => [
			'cpcssActive'                         => true,
			'autoptimizeAggregateInlineCSSActive' => 'on',
			'dismissed'                           => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesCPCSS' => [
		'config'   => [
			'cpcssActive'                         => false,
			'autoptimizeAggregateInlineCSSActive' => 'on',
			'dismissed'                           => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesAggregateInlineCss' => [
		'config'   => [
			'cpcssActive'                         => true,
			'autoptimizeAggregateInlineCSSActive' => 'off',
			'dismissed'                           => true,
		],
		'expected' => '',
	],
];
