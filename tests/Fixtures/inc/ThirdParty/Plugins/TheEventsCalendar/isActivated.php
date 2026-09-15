<?php

return [
	'shouldReturnFalseWhenTheEventsCalendarNotPresent' => [
		'config'   => [ 'tribe_events_file' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenTheEventsCalendarPresent'     => [
		'config'   => [ 'tribe_events_file' => '/path/to/the-events-calendar.php' ],
		'expected' => true,
	],
];
