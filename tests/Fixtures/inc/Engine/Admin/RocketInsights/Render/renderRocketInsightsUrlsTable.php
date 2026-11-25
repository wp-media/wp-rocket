<?php

return [
	'need_help_is_always_printed' => [
		'config'   => [],
		'expected' => [
			'contains'     => [ 'Performance Summary', 'data-wap-prompt="I need help with &#039;Rocket Insights&#039; &gt; &#039;Performance Summary&#039;"', 'wpr-infoAction--help', 'Need Help?', 'data-wpr_track_context="Rocket Insights"' ],
			'not_contains' => [],
		],
	],
];
