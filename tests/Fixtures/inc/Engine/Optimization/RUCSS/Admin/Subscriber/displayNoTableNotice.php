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
			'remove_unused_css' => true
		],
		'expected' => [
			'contains' => true,
			'content' => $content
		]
	],
	'disableShouldDoNothing' => [
		'config' => [
			'remove_unused_css' => false
		],
		'expected' => [
			'contains' => false,
			'content' => $content
		]
	],
];
