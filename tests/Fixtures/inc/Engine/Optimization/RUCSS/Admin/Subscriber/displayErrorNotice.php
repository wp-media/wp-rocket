<?php

$content = <<<Notice
<div class="notice notice-error " id="rocket-notice-rucss-error-http">
<p>
<strong>
WP Rocket</strong>
:<p>
It seems a security plugin or the server's firewall prevents WP Rocket from accessing the Remove Unused CSS generator. The following IP address 135.125.83.227 should be allowlisted:</p>
<ul>
<li>
in the security plugin, if you are using one</li>
<li>
the server's firewall - your host can help you with this</li>
</ul>
</p>
</div>
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
