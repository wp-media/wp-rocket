<?php

return [
	'shouldReturnFalseWhenNeitherPresent' => [
		'config'   => [
			'define_as3cf_init'     => false,
			'define_as3cf_pro_init' => false,
		],
		'expected' => false,
	],
	'shouldReturnTrueWhenInitPresent'     => [
		'config'   => [
			'define_as3cf_init'     => true,
			'define_as3cf_pro_init' => false,
		],
		'expected' => true,
	],
	'shouldReturnTrueWhenProInitPresent'  => [
		'config'   => [
			'define_as3cf_init'     => false,
			'define_as3cf_pro_init' => true,
		],
		'expected' => true,
	],
];
