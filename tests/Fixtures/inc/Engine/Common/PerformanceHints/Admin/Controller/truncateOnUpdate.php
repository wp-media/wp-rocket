<?php

return [
	'testShouldDoNothingWhenVersionUnder3.16.1' => [
		'config'     => [
			'filter'       => true,
			'new_version'  => '3.16.2',
			'old_version'  => '3.16.1',
			'not_completed' => 0,
		],
		'expected' => false,
	],
	'testShouldTruncateWhenVersion3.16.1AndHigher' => [
		'config'     => [
			'filter'       => true,
			'new_version'  => '3.16.1',
			'old_version'  => '3.15.0',
			'not_completed' => 0,
		],
		'expected' => true,
	],
];
