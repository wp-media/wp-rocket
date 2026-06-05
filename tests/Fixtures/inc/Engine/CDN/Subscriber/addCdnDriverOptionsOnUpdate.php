<?php

return [
	'shouldAddOptionsWhenOldVersionIsBelow322'            => [
		'config'   => [
			'new_version' => '3.22.0',
			'old_version' => '3.21.9',
		],
		'expected' => [
			'byocdn'   => true,
			'rocketcdn' => true,
		],
	],
	'shouldAddOptionsWhenOldVersionIsWellBelow322'        => [
		'config'   => [
			'new_version' => '3.22.0',
			'old_version' => '3.18.0',
		],
		'expected' => [
			'byocdn'   => true,
			'rocketcdn' => true,
		],
	],
	'shouldNotModifyOptionsWhenOldVersionEquals322'       => [
		'config'   => [
			'new_version' => '3.23.0',
			'old_version' => '3.22.0',
		],
		'expected' => [
			'no_change' => true,
		],
	],
	'shouldNotModifyOptionsWhenOldVersionIsAbove322'      => [
		'config'   => [
			'new_version' => '3.23.0',
			'old_version' => '3.22.1',
		],
		'expected' => [
			'no_change' => true,
		],
	],
];
