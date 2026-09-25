<?php
return [
	'shouldWriteOnlyThePluginRulesWhenSwitchedOff' => [
		'config'   => [
			'maxcache' => 0,
		],
		'expected' => [
			'contains'     => [ 'RewriteRule' ],
			'not_contains' => [ 'maxcache_module', 'MaxCachePath' ],
		],
	],
	'shouldHandDeliveryToTheModuleWhenSwitchedOn'  => [
		'config'   => [
			'maxcache' => 1,
		],
		'expected' => [
			'contains'     => [
				'<IfModule maxcache_module>',
				'MaxCache On',
				'MaxCachePath /wp-content/cache/wp-rocket/{HTTP_HOST}',
			],
			// Exactly one component may answer a request: the plugin's own serving rules are gone
			// from the file while the module has it.
			'not_contains' => [ 'RewriteRule' ],
		],
	],
];
