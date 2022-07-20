<?php
$content = <<<Notice
<div class="notice notice-error " id="rocket-notice-rucss-missing-table">
<p>
<strong>
WP Rocket</strong>
:
<p>
WP Rocket could not create the wpr_table in the database which is/are necessary for the Remove Unused CSS feature to work. Please reach out to our support.</p>
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
