<?php

return [
	'testShouldReturnDefaultWhenOptionDisabled' => [
		'options'  => [
			'delay_js' => 0,
		],
		'plugins'  => [],
		'expected' => [],
	],
	'testShouldReturnUpdatedArrayWhenOptionEnabled' => [
		'options'  => [
			'delay_js' => 1,
		],
		'plugins'  => [],
		'expected' => [
			'wp-meteor' => 'wp-meteor/wp-meteor.php',
		],
	],
];
