<?php

declare( strict_types=1 );

$expected_html = <<<HTML
<div class="notice notice-warning ">
<p><strong>
We have detected that Autoptimize's Optimize CSS Code feature is enabled. WP Rocket's Remove Unused CSS will not be applied to the file it creates. We suggest disabling it to take full advantage of WP Rocket's Remove Unused CSS Execution.
</strong></p>
<p><a class="rocket-dismiss" href="http://example.org/wp-admin/admin-post.php?action=rocket_ignore&amp;box=warn_when_autoptimize_css_and_rucss_active&amp;_wpnonce=123456">
Dismiss this notice.</a></p>
</div>
HTML;

return [
	'shouldAddNoticeWhenAutoptimizeAutoptimizeCssOnAndRUCSSActivated' => [
		'config'   => [
			'rucssActive'          => true,
			'autoptimizeCssActive' => 'on',
			'dismissed'            => false,
		],
		'expected' => $expected_html
	],

	'shouldSkipWhenAutoptimizeAutoptimizeCssOffAndRUCSSNotActivated' => [
		'config'   => [
			'rucssActive'          => false,
			'autoptimizeCssActive' => 'off',
			'dismissed'            => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAutoptimizeCssOffAndRUCSSActivated' => [
		'config'   => [
			'rucssActive'          => true,
			'autoptimizeCssActive' => 'off',
			'dismissed'            => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenAutoptimizeAutoptimizeCssOnAndRUCSSNotActivated' => [
		'config'   => [
			'rucssActive'          => false,
			'autoptimizeCssActive' => 'on',
			'dismissed'            => false,
		],
		'expected' => '',
	],

	'shouldSkipWhenUserHasDismissedNotice' => [
		'config'   => [
			'rucssActive'          => true,
			'autoptimizeCssActive' => 'on',
			'dismissed'            => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesRUCSS' => [
		'config'   => [
			'rucssActive'          => false,
			'autoptimizeCssActive' => 'on',
			'dismissed'            => true,
		],
		'expected' => '',
	],

	'shouldClearDismissalWhenUserDeactivatesAutoptimizeCss' => [
		'config'   => [
			'rucssActive'          => true,
			'autoptimizeCssActive' => 'off',
			'dismissed'            => true,
		],
		'expected' => '',
	],
];
