<?php

return [
	'everything_allows'    => [
		'config'   => [
			'valid_key'   => true,
			'can_use_ai'  => true,
			'ai_filter'   => true,
			'chat_filter' => true,
		],
		'expected' => [ 'result' => true ],
	],
	'invalid_key'          => [
		'config'   => [
			'valid_key'  => false,
			'can_use_ai' => true,
		],
		'expected' => [ 'result' => false ],
	],
	'cannot_use_ai'        => [
		'config'   => [
			'valid_key'  => true,
			'can_use_ai' => false,
		],
		'expected' => [ 'result' => false ],
	],
	'assistant_filter_off' => [
		'config'   => [
			'valid_key'  => true,
			'can_use_ai' => true,
			'ai_filter'  => false,
		],
		'expected' => [ 'result' => false ],
	],
	'chat_filter_off'      => [
		'config'   => [
			'valid_key'   => true,
			'can_use_ai'  => true,
			'ai_filter'   => true,
			'chat_filter' => false,
		],
		'expected' => [ 'result' => false ],
	],
];
