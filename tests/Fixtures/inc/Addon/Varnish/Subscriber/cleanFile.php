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
];
