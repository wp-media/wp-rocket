<?php

return [
	'shouldNotExcludeTermlyResources' => [
		'config' => [
			'excluded' => [],
			'termly_display_auto_blocker' => 'off'
		],
		'expected' => []
	],
	'shouldExcludeTermlyResources' => [
		'config' => [
			'excluded' => [],
			'termly_display_auto_blocker' => 'on'
		],
		'expected' => [
			'app.termly.io/resource-blocker/(.*)',
		]
	]
];
