<?php
return [
	'forcedPauseEnabled'  => [
		'config'   => [ 'forced_pause_state' => true ],
		'expected' => true,
	],

	'forcedPauseDisabled' => [
		'config'   => [ 'forced_pause_state' => false ],
		'expected' => false,
	],
];
