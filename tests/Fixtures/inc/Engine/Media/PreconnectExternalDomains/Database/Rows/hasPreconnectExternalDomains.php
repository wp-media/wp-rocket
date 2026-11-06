<?php

return [
	'shouldReturnFalseWhenStatusIsNotCompleted' => [
		'config' => [
			'status' => 'in-progress',
			'domains' => '["http://example.org", "http://example.com"]',
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenDomainsIsEmpty' => [
		'config' => [
			'status' => 'completed',
			'domains' => '',
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenDomainsIsEmptyArray' => [
		'config' => [
			'status' => 'completed',
			'domains' => '[]',
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenStatusIsCompletedAndDomainsIsNotEmpty' => [
		'config' => [
			'status' => 'completed',
			'domains' => '["http://example.org", "http://example.com"]',
		],
		'expected' => true,
	],
];
