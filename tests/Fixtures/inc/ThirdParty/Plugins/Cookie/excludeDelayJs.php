<?php

return [
	'shouldExcludeTermlyResources' => [
		'config' => [
			'excluded' => [],
			'termly_display_auto_blocker' => 'on'
		],
		'excluded' => [
			'app.termly.io/resource-blocker/(.*)',
		]
	]
];
