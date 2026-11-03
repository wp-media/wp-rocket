<?php

return [
	'valid_key_and_can_use_ai'   => [
		'config'   => [
			'valid_key'          => true,
			'call_get_user_data' => true,
			'user_data'          => [ 'can_use_ai' => true ],
			'input'              => true,
		],
		'expected' => [ 'result' => true ],
	],
	'flag_missing_defaults_true' => [
		'config'   => [
			'valid_key'          => true,
			'call_get_user_data' => true,
			'user_data'          => [],
			'input'              => true,
		],
		'expected' => [ 'result' => true ],
	],
	'flag_not_true'              => [
		'config'   => [
			'valid_key'          => true,
			'call_get_user_data' => true,
			'user_data'          => [ 'can_use_ai' => false ],
			'input'              => true,
		],
		'expected' => [ 'result' => false ],
	],
	'invalid_key_skips_user_data' => [
		'config'   => [
			'valid_key'          => false,
			'call_get_user_data' => false,
			'user_data'          => [],
			'input'              => true,
		],
		'expected' => [ 'result' => false ],
	],
	'earlier_false_wins'         => [
		'config'   => [
			'valid_key'          => true,
			'call_get_user_data' => true,
			'user_data'          => [ 'can_use_ai' => true ],
			'input'              => false,
		],
		'expected' => [ 'result' => false ],
	],
];
