<?php

return [
	'shouldDequeueOnWpRocketSettingsPage' => [
		'config'   => [ 'screen_id' => 'settings_page_wprocket' ],
		'expected' => [ 'dequeued' => true ],
	],
	'shouldNotDequeueOnAnyOtherScreen'    => [
		'config'   => [ 'screen_id' => 'dashboard' ],
		'expected' => [ 'dequeued' => false ],
	],
];
