<?php

return [
	'shouldReturnFalseWhenNeitherPresent'      => [
		'config'   => [
			'define_version' => false,
			'define_manager' => false,
		],
		'expected' => false,
	],
	'shouldReturnFalseWhenOnlyVersionPresent'  => [
		'config'   => [
			'define_version' => true,
			'define_manager' => false,
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenVersionAndManager'    => [
		'config'   => [
			'define_version' => true,
			'define_manager' => true,
		],
		'expected' => true,
	],
];
