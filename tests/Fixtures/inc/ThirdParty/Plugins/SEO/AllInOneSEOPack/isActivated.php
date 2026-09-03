<?php

return [
	'shouldReturnFalseWhenNeitherVersionPresent'      => [
		'config'   => [
			'define_aioseop_version' => false,
			'define_aioseo_version'  => false,
			'define_aioseo_function' => false,
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenV3Only'                       => [
		'config'   => [
			'define_aioseop_version' => true,
			'define_aioseo_version'  => false,
			'define_aioseo_function' => false,
		],
		'expected' => true,
	],
	'shouldReturnTrueWhenV4OnlyWithAioseoFunction'     => [
		'config'   => [
			'define_aioseop_version' => false,
			'define_aioseo_version'  => true,
			'define_aioseo_function' => true,
		],
		'expected' => true,
	],
	'shouldReturnFalseWhenV4OnlyWithoutAioseoFunction' => [
		'config'   => [
			'define_aioseop_version' => false,
			'define_aioseo_version'  => true,
			'define_aioseo_function' => false,
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenBothV3AndV4Present'           => [
		'config'   => [
			'define_aioseop_version' => true,
			'define_aioseo_version'  => true,
			'define_aioseo_function' => true,
		],
		'expected' => true,
	],
];
