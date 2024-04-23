<?php

return [
	'shouldReturnSameWhenRUCSSDisabled' => [
		'config' => [
			'remove_unused_css' => 0,
			'transient'         => false,
			'data'              => [],
		],
		'expected' => [],
	],
	'shouldReturnSameWhenNoTransient' => [
		'config' => [
			'remove_unused_css' => 1,
			'transient'         => false,
			'data'              => [],
		],
		'expected' => [],
	],
	'shouldReturnUpdatedWhenTransient' => [
		'config' => [
			'remove_unused_css' => 1,
			'transient'         => time(),
			'data'              => [],
		],
		'expected' => [
			'notice_end_time' => time(),
			'cron_disabled'   => false
		],
	],
];
