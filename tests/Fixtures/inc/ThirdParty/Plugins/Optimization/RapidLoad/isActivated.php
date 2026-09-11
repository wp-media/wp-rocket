<?php

return [
	'shouldReturnFalseWhenUucssVersionNotDefined' => [
		'config'   => [ 'define_uucss_version' => false ],
		'expected' => false,
	],
	'shouldReturnTrueWhenUucssVersionPresent'     => [
		'config'   => [ 'define_uucss_version' => true ],
		'expected' => true,
	],
];
