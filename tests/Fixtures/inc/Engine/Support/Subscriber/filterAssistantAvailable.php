<?php

return [
	'valid_key_and_can_use_ai'   => [
		'config'   => [
			'valid_key'        => true,
			'call_can_use_ai'  => true,
			'can_use_ai'       => true,
			'input'            => true,
		],
		'expected' => [ 'result' => true ],
	],
	'user_cannot_use_ai'       => [
		'config'   => [
			'valid_key'        => true,
			'call_can_use_ai'  => true,
			'can_use_ai'       => false,
			'input'            => true,
		],
		'expected' => [ 'result' => false ],
	],
	'invalid_key_skips_user' => [
		'config'   => [
			'valid_key'        => false,
			'call_can_use_ai'  => false,
			'can_use_ai'       => false,
			'input'            => true,
		],
		'expected' => [ 'result' => false ],
	],
	'earlier_false_wins'         => [
		'config'   => [
			'valid_key'        => true,
			'call_can_use_ai'  => false,
			'can_use_ai'       => true,
			'input'            => false,
		],
		'expected' => [ 'result' => false ],
	],
];
