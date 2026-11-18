<?php

return [
	'need_help_is_always_printed' => [
		'config'   => [],
		'expected' => [
			'contains'     => [ 'Performance Summary', 'data-wap-prompt', 'wpr-infoAction--help', 'Need Help?' ],
			'not_contains' => [],
		],
	],
];
