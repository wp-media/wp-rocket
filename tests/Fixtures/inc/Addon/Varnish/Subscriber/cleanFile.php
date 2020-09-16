<?php
return [
	'testShouldDoNothingWhenVarnishDisabled' => [
		'config' => [
			'filter' => false,
			'option' => 0,
		],
		'expected' => false,
	],
	'testShouldPurgeOnceWhenFilterEnabled' => [
		'config' => [
			'filter' => true,
			'option' => 0,
		],
		'expected' => true,
	],
	'testShouldPurgeOnceWhenVarnishEnabled' => [
		'config' => [
			'filter' => false,
			'option' => 1,
		],
		'expected' => true,
	],
];
