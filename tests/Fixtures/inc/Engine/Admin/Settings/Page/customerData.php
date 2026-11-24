<?php

return [
	'user_cannot_use_assistant' => [
		'config'   => [
			'user_data'      => [ 'can_use_ai' => false ],
			'filter_returns' => false,
		],
		'expected' => [
			'has_assistant_available' => true,
			'assistant_available'     => false,
		],
	],
	'assistant_available'       => [
		'config'   => [
			'user_data' => [ 'can_use_ai' => true ],
		],
		'expected' => [
			'has_assistant_available' => true,
			'assistant_available'     => true,
		],
	],
	'user_data_unreadable'      => [
		'config'   => [
			'user_data' => false,
		],
		'expected' => [
			'has_assistant_available' => false,
		],
	],
];
