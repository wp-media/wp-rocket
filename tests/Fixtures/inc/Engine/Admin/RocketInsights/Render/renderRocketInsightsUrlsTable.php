<?php

return [
	'need_help_is_always_printed' => [
		'config'   => [],
		'expected' => [
			'contains'     => [ 'Performance Summary', 'data-wap-prompt', 'wpr-infoAction--help', 'Need Help?', 'data-wpr_track_context="Rocket Insights"' ],
			'not_contains' => [],
		],
	],
];
