<?php
return [
	'testBeforeRocketCleanFileShouldDoNothingWhenVarnishDisabled' => [
		'config' => [
			'filter' => false,
			'option' => 0,
			'hook' => 'before_rocket_clean_file',
			'arg' => 'http://example.org/about/',
		],
		'expected' => false,
	],
	'testBeforeRocketCleanFileShouldPurgeOnceWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'option' => 0,
			'hook' => 'before_rocket_clean_file',
			'arg' => 'http://example.org/about/',
		],
		'expected' => true,
	],
	'testBeforeRocketCleanFileShouldPurgeOnceWhenVarnishEnabled' => [
		'config' => [
			'filter' => false,
			'option' => 1,
			'hook' => 'before_rocket_clean_file',
			'arg' => 'http://example.org/about/',
		],
		'expected' => true,
	],
	'testRocketRucssAfterClearingUsedcssShouldDoNothingWhenVarnishDisabled' => [
		'config' => [
			'filter' => false,
			'option' => 0,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'http://example.org/about/',
		],
		'expected' => false,
	],
	'testRocketRucssAfterClearingUsedcssShouldPurgeOnceWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'option' => 0,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'http://example.org/about/',
		],
		'expected' => true,
	],
	'testRocketRucssAfterClearingUsedcssShouldPurgeOnceWhenVarnishEnabled' => [
		'config' => [
			'filter' => false,
			'option' => 1,
			'hook' => 'rocket_rucss_after_clearing_usedcss',
			'arg' => 'http://example.org/about/',
		],
		'expected' => true,
	],
	'testRocketPerformanceHintDataShouldPurgeOnceWhenVarnishEnabled' => [
		'config' => [
			'filter' => false,
			'hook' => 'rocket_performance_hints_data_after_clearing',
			'arg' => 'http://example.org/about/',
			'option' => 1,
		],
		'expected' => true,
	],
	'testRocketPerformanceHintDataShouldPurgeOnceWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'hook' => 'rocket_performance_hints_data_after_clearing',
			'arg' => 'http://example.org/about/',
			'option' => 0,
		],
		'expected' => true,
	]
];
