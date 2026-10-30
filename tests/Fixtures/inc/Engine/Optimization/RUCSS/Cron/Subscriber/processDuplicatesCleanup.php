<?php

return [
	'test_data' => [
		'shouldRescheduleInAMinuteWhenPartial' => [
			'config'   => [ 'result' => 'partial' ],
			'expected' => 60,
		],
		'shouldRescheduleInAnHourOnError'      => [
			'config'   => [ 'result' => 'error' ],
			'expected' => 3600,
		],
		'shouldNotRescheduleWhenLocked'        => [
			'config'   => [ 'result' => 'locked' ],
			'expected' => false,
		],
		'shouldNotRescheduleWhenDone'          => [
			'config'   => [ 'result' => 'done' ],
			'expected' => false,
		],
	],
];
