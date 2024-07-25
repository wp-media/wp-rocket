<?php
$content = <<<Notice
<div class="notice notice-error " id="rocket-notice-rucss-missing-table">
<p>
<strong>
WP Rocket</strong>
: Could not create the wpr_table table in the database which is necessary for the Remove Unused CSS feature to work. Please reach out to <a href="https://docs.wp-rocket.me/article/1828-could-not-create-the-rucss-usedcss-table/?utm_source=wp_plugin&#038;utm_medium=wp_rocket" target="_blank" rel="noopener">our support</a>.</p>
</div>
Notice;

return [
	'testShouldDoNothingWhenRucssDisabled' => [
		'config' => [
			'remove_unused_css' => false,

		],
		'expected' => [
			'contains' => false,
			'content' => $content,
		],
	],
	'testShouldDisplayNoticeWhenTableNotExists' => [
		'config' => [
			'remove_unused_css' => true,
		],
		'expected' => [
			'contains' => true,
			'content' => $content,
		],
	],
];
