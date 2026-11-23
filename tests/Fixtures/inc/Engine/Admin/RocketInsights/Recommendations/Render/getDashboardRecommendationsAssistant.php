<?php

return [
	'need_help_is_always_printed' => [
		'config'   => [],
		'expected' => [
			'contains'     => [ 'wpr-dash-section-header__actions', 'data-wap-prompt', 'wpr-infoAction--help', 'Need Help?', 'data-wpr_track_context="Recommendations"' ],
			'not_contains' => [],
		],
	],
];
