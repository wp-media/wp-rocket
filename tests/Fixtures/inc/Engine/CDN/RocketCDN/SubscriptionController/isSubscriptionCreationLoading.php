<?php
return [
	'noTokenTransientSet'  => [
		'config'   => [
			'has_token'     => false,
			'has_transient' => true,
		],
		'expected' => false,
	],

	'tokenNoTransient'     => [
		'config'   => [
			'has_token'     => true,
			'has_transient' => false,
		],
		'expected' => false,
	],

	'tokenAndTransientSet' => [
		'config'   => [
			'has_token'     => true,
			'has_transient' => true,
		],
		'expected' => true,
	],
];
