<?php

return [
	'shouldReturnFalseWhenPerfmattersNotPresent' => [
		'config'   => [ 'perfmatters_version' => null ],
		'expected' => false,
	],
	'shouldReturnTrueWhenPerfmattersPresent'     => [
		'config'   => [ 'perfmatters_version' => '1.6' ],
		'expected' => true,
	],
];
