<?php

return [
	'removes cdn when present'         => [
		'config'   => [
			'options' => [ 'active_tags', 'cdn', 'cache_mobile' ],
		],
		'expected' => [ 'active_tags', 'cache_mobile' ],
	],
	'leaves list untouched when absent' => [
		'config'   => [
			'options' => [ 'active_tags', 'cache_mobile' ],
		],
		'expected' => [ 'active_tags', 'cache_mobile' ],
	],
	'handles empty list'                => [
		'config'   => [
			'options' => [],
		],
		'expected' => [],
	],
];
