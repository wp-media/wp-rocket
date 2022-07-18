<?php
$content = <<<Notice
<div class="notice notice-error " id="rocket-notice-rucss-missing-table">
<p>
<strong>
WP Rocket</strong>
:<p>
We detected missing database table related to the Remove Unused CSS feature.</p>
</p>
</div>
Notice;


return [
	'tableExistsShouldDisplayNotice' => [
		'config' => [
			'table_exists' => true,
			'remove_unused_css' => true
		],
		'expected' => [
			'contains' => true,
			'content' => $content
		]
	],
	'tableNotExistsShouldDoNothing' => [
		'config' => [
			'table_exists' => false,
			'remove_unused_css' => true
		],
		'expected' => [
			'contains' => false,
			'content' => $content
		]
	],
	'disableShouldDoNothing' => [
		'config' => [
			'table_exists' => true,
			'remove_unused_css' => false
		],
		'expected' => [
			'contains' => false,
			'content' => $content
		]
	],
];
