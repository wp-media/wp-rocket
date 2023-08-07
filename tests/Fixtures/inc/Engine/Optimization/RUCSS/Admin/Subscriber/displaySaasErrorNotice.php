<?php

$content = <<<Notice
<div class="notice notice-error " id="rocket-notice-rucss-error-http">
<p>
<strong>
WP Rocket</strong>
: It seems a security plugin or the server's firewall prevents WP Rocket from accessing the Remove Unused CSS generator. IPs listed <a href="https://docs.wp-rocket.me/article/1529-remove-unused-css?utm_source=wp_plugin&#038;utm_medium=wp_rocket#basic-requirements" data-beacon-article="6076083ff8c0ef2d98df1f97" rel="noopener noreferrer" target="_blank">here in our documentation</a> should be added to your allowlists:
<ul>
<li>
- In the security plugin, if you are using one</li>
<li>
- In the server's firewall. Your host can help you with this</li>
</ul>
</p>
<p>
<a class="rocket-dismiss button-primary"
Notice;


return [
	'transientExistsShouldDisplayNotice' => [
		'config' => [
			'transient_exists' => true,
			'remove_unused_css' => true
		],
		'expected' => [
			'contains' => true,
			'content' => $content
		]
	],
	'transientNotExistsShouldDoNothing' => [
		'config' => [
			'transient_exists' => false,
			'remove_unused_css' => true
		],
		'expected' => [
			'contains' => false,
			'content' => $content
		]
	],
	'disableShouldDoNothing' => [
		'config' => [
			'transient_exists' => true,
			'remove_unused_css' => false
		],
		'expected' => [
			'contains' => false,
			'content' => $content
		]
	],
];
