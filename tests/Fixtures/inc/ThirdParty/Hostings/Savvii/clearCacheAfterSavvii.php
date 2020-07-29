<?php

return [
	'testShouldDoNothing' => [
		'config'   => [],
		'expected' => false,
	],
	'testShouldClearCacheWhenFlush' => [
		'config'   => [
			'warpdrive_flush_now' => true,
		],
		'expected' => true,
	],
	'testShouldClearCacheWhenDomainFlush' => [
		'config'   => [
			'warpdrive_domainflush_now' => true,
		],
		'warpdrive_domainflush_now' => true,
		'expected' => true,
	],
];
