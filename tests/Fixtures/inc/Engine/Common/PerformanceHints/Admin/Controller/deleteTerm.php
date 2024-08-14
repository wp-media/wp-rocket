<?php

return [
	'testShoulDoNothingWhenDisabled' => [
		'config' => [
			'filter' => false,
			'term_id' => 1,
			'url' => 'http://example.org/term',
		],
		'expected' => false,
	],
	'testShoulDoNothingURLFalse' => [
		'config' => [
			'filter' => true,
			'term_id' => 1,
			'url' => false,
		],
		'expected' => false,
	],
	'testShoulDeleteTerm' => [
		'config' => [
			'filter' => true,
			'term_id' => 1,
			'url' => 'http://example.org/term',
		],
		'expected' => true,
	],
];
